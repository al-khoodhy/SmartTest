<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\MataKuliah;
use App\Models\JawabanMahasiswa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TugasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'voyager.permission:view_tugas']);
    }
    
    /**
     * Display kumpulan tugas mahasiswa
     */
    public function index(Request $request)
    {
        $mahasiswa = auth()->user();
        
        // Get mata kuliah yang diambil mahasiswa
        $kelasIds = $mahasiswa->enrollments()->active()->pluck('kelas_id');
        
        $query = Tugas::whereIn('kelas_id', $kelasIds)
            ->with(['kelas.mataKuliah', 'dosen'])
            ->active();
        
        // Filter berdasarkan mata kuliah
        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }
        
        // Filter berdasarkan status
        if ($request->status) {
            switch ($request->status) {
                case 'available':
                    // Tugas yang belum dikerjakan dan belum deadline
                    $tugasDikerjakan = $mahasiswa->jawabanMahasiswa()->pluck('tugas_id');
                    $query->where('deadline', '>', Carbon::now())
                          ->whereNotIn('id', $tugasDikerjakan);
                    break;
                case 'in_progress':
                    // Tugas yang sedang dikerjakan (draft)
                    $tugasDraft = $mahasiswa->jawabanMahasiswa()
                        ->where('status', 'draft')
                        ->pluck('tugas_id');
                    $query->whereIn('id', $tugasDraft);
                    break;
                case 'submitted':
                    // Tugas yang sudah disubmit
                    $tugasSubmitted = $mahasiswa->jawabanMahasiswa()
                        ->whereIn('status', ['submitted', 'graded'])
                        ->pluck('tugas_id');
                    $query->whereIn('id', $tugasSubmitted);
                    break;
                case 'expired':
                    // Tugas yang sudah deadline tapi belum dikerjakan
                    $tugasDikerjakan = $mahasiswa->jawabanMahasiswa()->pluck('tugas_id');
                    $query->where('deadline', '<', Carbon::now())
                          ->whereNotIn('id', $tugasDikerjakan);
                    break;
            }
        }
        
        $tugas = $query->latest()->paginate(10);
        
        // Get mata kuliah untuk filter
        $mataKuliah = MataKuliah::whereIn('id', $kelasIds)->active()->get();
        
        // Get status jawaban untuk setiap tugas
        $jawabanStatus = [];
        foreach ($tugas as $t) {
            $jawaban = $mahasiswa->jawabanMahasiswa()
                ->where('tugas_id', $t->id)
                ->first();
            $jawabanStatus[$t->id] = $jawaban;
        }
        
        return view('mahasiswa.tugas.index', compact('tugas', 'mataKuliah', 'jawabanStatus'));
    }
    
    /**
     * Show detail tugas
     */
    public function show(Tugas $tugas)
    {
        $this->authorize('view', $tugas);
        
        $mahasiswa = auth()->user();
        
        // Check apakah mahasiswa terdaftar di mata kuliah ini
        $isEnrolled = $mahasiswa->enrollments()
            ->where('kelas_id', $tugas->kelas_id)
            ->where('status', 'active')
            ->exists();
        
        if (!$isEnrolled) {
            abort(403, 'Anda tidak terdaftar di mata kuliah ini.');
        }
        
        $tugas->load(['kelas.mataKuliah', 'soal']);
        
        // Get jawaban mahasiswa jika ada
        $jawaban = $mahasiswa->jawabanMahasiswa()
            ->where('tugas_id', $tugas->id)
            ->with(['penilaian', 'jawabanSoal.soal', 'jawabanSoal.penilaian'])
            ->first();
        
        // Check apakah tugas masih bisa dikerjakan
        $canWork = !$jawaban && $tugas->deadline > Carbon::now() && $tugas->is_active;
        $canContinue = $jawaban && $jawaban->status === 'draft' && $tugas->deadline > Carbon::now();
        $isExpired = $tugas->deadline <= Carbon::now();
        
        return view('mahasiswa.tugas.show', compact('tugas', 'jawaban', 'canWork', 'canContinue', 'isExpired'));
    }
    
    /**
     * Start working on tugas
     */
    public function start(Tugas $tugas)
    {
        $this->authorize('view', $tugas);
        
        $mahasiswa = auth()->user();
        
        // Check apakah mahasiswa terdaftar di mata kuliah ini
        $isEnrolled = $mahasiswa->enrollments()
            ->where('kelas_id', $tugas->kelas_id)
            ->where('status', 'active')
            ->exists();
        
        if (!$isEnrolled) {
            abort(403, 'Anda tidak terdaftar di mata kuliah ini.');
        }
        
        // Check apakah tugas masih aktif dan belum deadline
        if (!$tugas->is_active || $tugas->deadline <= Carbon::now()) {
            return redirect()->route('mahasiswa.tugas.show', $tugas)
                ->with('error', 'Tugas sudah tidak aktif atau sudah melewati deadline.');
        }
        
        // Check apakah sudah ada jawaban
        $existingJawaban = $mahasiswa->jawabanMahasiswa()
            ->where('tugas_id', $tugas->id)
            ->first();
        
        if ($existingJawaban) {
            if ($existingJawaban->status === 'draft') {
                // Continue existing draft
                return redirect()->route('mahasiswa.ujian.work', $existingJawaban);
            } else {
                // Already submitted
                return redirect()->route('mahasiswa.tugas.show', $tugas)
                    ->with('error', 'Anda sudah mengerjakan tugas ini.');
            }
        }
        
        // Create new jawaban
        $jawaban = JawabanMahasiswa::create([
            'tugas_id' => $tugas->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jawaban' => '',
            'waktu_mulai' => Carbon::now(),
            'status' => 'draft'
        ]);
        
        return redirect()->route('mahasiswa.ujian.work', $jawaban);
    }
}
