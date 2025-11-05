<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Services\AutoGradingService;
use App\Jobs\ProcessAutoGrading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PenilaianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:2']);
    }
    
    /**
     * Display penilaian dashboard
     */
    public function index(Request $request)
    {
        $dosen = auth()->user();
        
        $tugasQuery = Tugas::where('dosen_id', $dosen->id)
            ->with(['kelas.mataKuliah', 'jawabanMahasiswa.penilaian']);
        if ($request->kelas_id) {
            $tugasQuery->where('kelas_id', $request->kelas_id);
        }
        $tugas = $tugasQuery->latest()->paginate(10);
        $kelas = $dosen->kelasAsDosen()->with('mataKuliah')->get();
        $totalJawaban = JawabanMahasiswa::whereHas('tugas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->where('status', 'submitted')->count();
        $sudahDinilai = JawabanMahasiswa::whereHas('tugas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->where('status', 'graded')->count();
        $menungguPenilaian = $totalJawaban - $sudahDinilai;
        return view('dosen.penilaian.index', compact('tugas', 'kelas', 'totalJawaban', 'sudahDinilai', 'menungguPenilaian'));
    }
    
    /**
     * Show jawaban untuk tugas tertentu
     */
    public function showTugas(Tugas $tugas)
    {
        $this->authorize('view', $tugas);
        
        $jawaban = $tugas->jawabanMahasiswa()
            ->with(['mahasiswa', 'penilaian', 'jawabanSoal.soal', 'jawabanSoal.penilaian'])
            ->where('status', '!=', 'draft')
            ->latest()
            ->paginate(15);
        
        return view('dosen.penilaian.tugas', compact('tugas', 'jawaban'));
    }
    
    /**
     * Show detail jawaban mahasiswa
     */
    public function showJawaban(JawabanMahasiswa $jawaban)
    {
        $this->authorize('view', $jawaban->tugas);
        
        $jawaban->load(['tugas', 'mahasiswa', 'penilaian']);
        
        return view('dosen.penilaian.jawaban', compact('jawaban'));
    }
    
    /**
     * Manual grading form
     */
    public function grade(JawabanMahasiswa $jawaban)
    {
        $this->authorize('view', $jawaban->tugas);
        $jawaban->load(['tugas', 'mahasiswa', 'jawabanSoal.soal', 'jawabanSoal.penilaian']);
        return view('dosen.penilaian.grade', compact('jawaban'));
    }
    
    /**
     * Store manual grading
     */
    public function storeGrade(Request $request, JawabanMahasiswa $jawaban)
    {
        $this->authorize('view', $jawaban->tugas);
        $jawaban->load(['jawabanSoal.soal', 'jawabanSoal.penilaian']);
        
        $rules = [];
        foreach ($jawaban->jawabanSoal as $jawabanSoal) {
            $rules['nilai_manual.' . $jawabanSoal->id] = 'nullable|numeric|max:' . $jawaban->tugas->nilai_maksimal;
            $rules['feedback_manual.' . $jawabanSoal->id] = 'nullable|string';
        }
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Update penilaian per soal
        $hasManualGrade = false;
        foreach ($jawaban->jawabanSoal as $jawabanSoal) {
            $nilai = $request->input('nilai_manual.' . $jawabanSoal->id);
            $feedback = $request->input('feedback_manual.' . $jawabanSoal->id);
            
            // Jika nilai kosong, set ke null
            if ($nilai === null || $nilai === '') {
                $nilai = null;
            } else {
                // Pastikan nilai tidak melebihi nilai maksimal
                $nilai = min($nilai, $jawaban->tugas->nilai_maksimal);
                $hasManualGrade = true; // Ada nilai manual yang diinput
            }
            
            // Jika feedback kosong, set ke null
            if ($feedback === null || $feedback === '') {
                $feedback = null;
            }
            
            $penilaian = $jawabanSoal->penilaian;
            if (!$penilaian) {
                $penilaian = \App\Models\PenilaianSoal::create([
                    'jawaban_soal_id' => $jawabanSoal->id,
                    'nilai_manual' => $nilai,
                    'nilai_final' => $nilai, // Nilai manual menjadi nilai final
                    'feedback_manual' => $feedback,
                    'status_penilaian' => $nilai !== null ? 'final' : 'pending',
                    'graded_by' => auth()->id(),
                    'graded_at' => now(),
                ]);
            } else {
                $penilaian->update([
                    'nilai_manual' => $nilai,
                    'nilai_final' => $nilai, // Nilai manual menjadi nilai final
                    'feedback_manual' => $feedback,
                    'status_penilaian' => $nilai !== null ? 'final' : 'pending',
                    'graded_by' => auth()->id(),
                    'graded_at' => now(),
                ]);
            }
        }
        
        // Hitung nilai akhir berdasarkan PenilaianSoal
        $nilaiAkhir = $jawaban->nilai_akhir;
        
        // Update atau buat Penilaian utama sebagai backup/arsip
        $penilaianUtama = $jawaban->penilaian;
        if (!$penilaianUtama) {
            $penilaianUtama = \App\Models\Penilaian::create([
                'jawaban_id' => $jawaban->id,
                'nilai_manual' => $nilaiAkhir,
                'nilai_final' => $nilaiAkhir,
                'feedback_manual' => 'Nilai dihitung otomatis dari penilaian per soal',
                'status_penilaian' => 'final',
                'graded_by' => auth()->id(),
                'graded_at' => now(),
            ]);
        } else {
            $penilaianUtama->update([
                'nilai_manual' => $nilaiAkhir,
                'nilai_final' => $nilaiAkhir,
                'feedback_manual' => 'Nilai dihitung otomatis dari penilaian per soal',
                'status_penilaian' => 'final',
                'graded_by' => auth()->id(),
                'graded_at' => now(),
            ]);
        }
        
        // Update status jawaban menjadi graded jika semua soal sudah dinilai (manual/AI)
        $isAllGraded = $jawaban->jawabanSoal->every(function($js) {
            $penilaian = $js->penilaian;
            return $penilaian && in_array($penilaian->status_penilaian, ['final', 'ai_graded']);
        });
        if ($isAllGraded && $jawaban->status !== 'graded') {
            $jawaban->update(['status' => 'graded']);
        }
        
        return redirect()->route('dosen.penilaian.tugas', $jawaban->tugas)
            ->with('success', 'Penilaian berhasil disimpan. Nilai akhir: ' . $nilaiAkhir);
    }
    
    /**
     * Trigger auto grading untuk tugas
     */
    public function autoGrade(Tugas $tugas, AutoGradingService $autoGradingService)
    {
        $this->authorize('view', $tugas);
        
        if (!$tugas->auto_grade) {
            return redirect()->back()
                ->with('error', 'Tugas ini tidak menggunakan auto grading.');
        }
        
        try {
            $result = $autoGradingService->gradeAllPendingForTugas($tugas);
            
            $message = "Auto grading selesai. ";
            $message .= "Berhasil: {$result['success_count']}, ";
            $message .= "Gagal: {$result['error_count']}";
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menjalankan auto grading: ' . $e->getMessage());
        }
    }
    
    /**
     * Re-grade jawaban dengan AI
     */
    public function regrade(JawabanMahasiswa $jawaban, AutoGradingService $autoGradingService)
    {
        $this->authorize('view', $jawaban->tugas);
        
        if (!$jawaban->tugas->auto_grade) {
            return redirect()->back()
                ->with('error', 'Tugas ini tidak menggunakan auto grading.');
        }
        
        try {
            $autoGradingService->regradeJawaban($jawaban);
            
            return redirect()->back()
                ->with('success', 'Jawaban berhasil dinilai ulang dengan AI.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menilai ulang: ' . $e->getMessage());
        }
    }
    
    /**
     * Export nilai tugas
     */
    public function exportNilai(Tugas $tugas)
    {
        $this->authorize('view', $tugas);
        
        // Load relasi yang diperlukan
        $tugas->load(['kelas.mataKuliah']);
        
        $jawaban = $tugas->jawabanMahasiswa()
            ->with(['mahasiswa', 'penilaian', 'jawabanSoal.soal', 'jawabanSoal.penilaian'])
            ->where('status', 'graded')
            ->get();
        
        $filename = 'nilai_' . str_replace(' ', '_', $tugas->judul) . '_' . date('Y-m-d') . '.xlsx';
        
        // Generate Excel file
        $excelData = $this->generateExcel($tugas, $jawaban);
        
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];
        
        return response($excelData, 200, $headers);
    }
    
    /**
     * Generate Excel file content
     */
    private function generateExcel(Tugas $tugas, $jawaban)
    {
        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $zip = new \ZipArchive();
        $zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        // Create directory structure
        $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->getRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->getWorkbookXml());
        $zip->addFromString('xl/styles.xml', $this->getStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->getSheetXml($tugas, $jawaban));
        
        $zip->close();
        
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        
        return $content;
    }
    
    private function getContentTypesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
    }
    
    private function getRelsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
    }
    
    private function getWorkbookRelsXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
    }
    
    private function getWorkbookXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Nilai Tugas" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
    }
    
    private function getStylesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><color rgb="000000"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><color rgb="000000"/><name val="Calibri"/></font>
    </fonts>
    <fills count="2">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
    </fills>
    <borders count="1">
        <border><left/><right/><top/><bottom/><diagonal/></border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
    </cellXfs>
</styleSheet>';
    }
    
    private function getSheetXml(Tugas $tugas, $jawaban)
    {
        $rows = [];
        
        // Header informasi tugas
        $rows[] = '<row><c t="inlineStr"><is><t>Mata Kuliah</t></is></c><c t="inlineStr"><is><t>' . htmlspecialchars($tugas->mataKuliah->nama_mk ?? '-') . '</t></is></c></row>';
        $rows[] = '<row><c t="inlineStr"><is><t>Kelas</t></is></c><c t="inlineStr"><is><t>' . htmlspecialchars($tugas->kelas->nama_kelas ?? '-') . '</t></is></c></row>';
        $rows[] = '<row><c t="inlineStr"><is><t>Judul Tugas</t></is></c><c t="inlineStr"><is><t>' . htmlspecialchars($tugas->judul) . '</t></is></c></row>';
        $rows[] = '<row></row>'; // Empty row
        
        // Header tabel
        $headerRow = '<row>';
        $headerRow .= '<c t="inlineStr" s="1"><is><t>No</t></is></c>';
        $headerRow .= '<c t="inlineStr" s="1"><is><t>Nama Mahasiswa</t></is></c>';
        $headerRow .= '<c t="inlineStr" s="1"><is><t>Nilai AI</t></is></c>';
        $headerRow .= '<c t="inlineStr" s="1"><is><t>Nilai Manual</t></is></c>';
        $headerRow .= '<c t="inlineStr" s="1"><is><t>Nilai Final</t></is></c>';
        $headerRow .= '<c t="inlineStr" s="1"><is><t>Status</t></is></c>';
        $headerRow .= '<c t="inlineStr" s="1"><is><t>Tanggal Submit</t></is></c>';
        $headerRow .= '</row>';
        $rows[] = $headerRow;
        
        // Data rows
        $no = 1;
        foreach ($jawaban as $j) {
            $row = '<row>';
            $row .= '<c><v>' . $no . '</v></c>';
            $row .= '<c t="inlineStr"><is><t>' . htmlspecialchars($j->mahasiswa->name ?? '-') . '</t></is></c>';
            // Nilai AI total (menggunakan accessor)
            $nilaiAi = $j->nilai_ai > 0 ? $j->nilai_ai : '';
            $row .= '<c><v>' . ($nilaiAi !== '' ? $nilaiAi : '') . '</v></c>';
            // Nilai Manual
            $nilaiManual = $j->nilai_manual > 0 ? $j->nilai_manual : '';
            $row .= '<c><v>' . ($nilaiManual !== '' ? $nilaiManual : '') . '</v></c>';
            // Nilai Final
            $nilaiFinal = $j->nilai_akhir > 0 ? $j->nilai_akhir : '';
            $row .= '<c><v>' . ($nilaiFinal !== '' ? $nilaiFinal : '') . '</v></c>';
            // Status
            $row .= '<c t="inlineStr"><is><t>' . htmlspecialchars($j->status ?? '-') . '</t></is></c>';
            // Tanggal Submit
            $tanggal = $j->waktu_selesai ? $j->waktu_selesai->format('Y-m-d H:i:s') : '-';
            $row .= '<c t="inlineStr"><is><t>' . htmlspecialchars($tanggal) . '</t></is></c>';
            $row .= '</row>';
            $rows[] = $row;
            $no++;
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheetData>
        ' . implode("\n        ", $rows) . '
    </sheetData>
</worksheet>';
        
        return $xml;
    }
}
