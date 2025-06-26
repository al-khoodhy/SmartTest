<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JawabanMahasiswa;
use App\Jobs\ProcessAutoGrading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UjianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:mahasiswa']);
    }
    
    /**
     * Show ujian interface
     */
    public function work(JawabanMahasiswa $jawaban)
    {
        $mahasiswa = auth()->user();
        
        // Check ownership
        if ($jawaban->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Anda tidak memiliki akses ke jawaban ini.');
        }
        
        // Check status
        if ($jawaban->status !== 'draft') {
            return redirect()->route('mahasiswa.tugas.show', $jawaban->tugas)
                ->with('error', 'Jawaban sudah disubmit dan tidak bisa diubah.');
        }
        
        $jawaban->load(['tugas.mataKuliah', 'tugas.dosen', 'tugas.soal', 'jawabanSoal']);
        
        // Check deadline
        if ($jawaban->tugas->deadline <= Carbon::now()) {
            return redirect()->route('mahasiswa.tugas.show', $jawaban->tugas)
                ->with('error', 'Waktu ujian sudah habis.');
        }
        
        // Calculate remaining time
        $waktuMulai = $jawaban->waktu_mulai;
        $durasiMenit = $jawaban->tugas->durasi_menit;
        $deadline = $jawaban->tugas->deadline;
        
        // Waktu selesai berdasarkan durasi atau deadline (yang lebih dulu)
        $waktuSelesaiDurasi = $waktuMulai->addMinutes($durasiMenit);
        $waktuSelesai = $waktuSelesaiDurasi->lt($deadline) ? $waktuSelesaiDurasi : $deadline;
        
        $sisaWaktu = Carbon::now()->diffInSeconds($waktuSelesai, false);
        
        if ($sisaWaktu <= 0) {
            // Auto submit jika waktu habis
            return $this->autoSubmit($jawaban);
        }
        
        return view('mahasiswa.ujian.work', compact('jawaban', 'sisaWaktu'));
    }
    
    /**
     * Save draft jawaban
     */
    public function saveDraft(Request $request, JawabanMahasiswa $jawaban)
    {
        $mahasiswa = auth()->user();
        
        // Check ownership
        if ($jawaban->mahasiswa_id !== $mahasiswa->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Check status
        if ($jawaban->status !== 'draft') {
            return response()->json(['error' => 'Jawaban sudah disubmit'], 400);
        }
        
        // Check deadline
        if ($jawaban->tugas->deadline <= Carbon::now()) {
            return response()->json(['error' => 'Waktu ujian sudah habis'], 400);
        }
        
        $data = $request->input('jawaban_soal', []);
        foreach ($jawaban->tugas->soal as $soal) {
            $jawab = $data[$soal->id] ?? '';
            $jawabanSoal = $jawaban->jawabanSoal()->firstOrNew(['soal_id' => $soal->id]);
            $jawabanSoal->jawaban = $jawab;
            $jawabanSoal->status = 'draft';
            $jawabanSoal->save();
        }
        $jawaban->updated_at = Carbon::now();
        $jawaban->save();
        return response()->json([
            'success' => true,
            'message' => 'Draft berhasil disimpan',
            'timestamp' => Carbon::now()->format('H:i:s')
        ]);
    }
    
    /**
     * Submit jawaban
     */
    public function submit(Request $request, JawabanMahasiswa $jawaban)
    {
        $mahasiswa = auth()->user();
        
        // Check ownership
        if ($jawaban->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Anda tidak memiliki akses ke jawaban ini.');
        }
        
        // Check status
        if ($jawaban->status !== 'draft') {
            return redirect()->route('mahasiswa.tugas.show', $jawaban->tugas)
                ->with('error', 'Jawaban sudah disubmit sebelumnya.');
        }
        
        $data = $request->input('jawaban_soal', []);
        $rules = [];
        foreach ($jawaban->tugas->soal as $soal) {
            $rules['jawaban_soal.' . $soal->id] = 'required|string|min:50';
        }
        $rules['confirm_submit'] = 'required|accepted';
        $validator = Validator::make($request->all(), $rules, [
            'jawaban_soal.*.min' => 'Jawaban minimal 50 karakter.',
            'confirm_submit.accepted' => 'Anda harus mengkonfirmasi submit jawaban.'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        foreach ($jawaban->tugas->soal as $soal) {
            $jawab = $data[$soal->id] ?? '';
            $jawabanSoal = $jawaban->jawabanSoal()->firstOrNew(['soal_id' => $soal->id]);
            $jawabanSoal->jawaban = $jawab;
            $jawabanSoal->status = 'submitted';
            $jawabanSoal->waktu_selesai = Carbon::now();
            $jawabanSoal->save();
        }
        $jawaban->waktu_selesai = Carbon::now();
        $jawaban->status = 'submitted';
        $jawaban->save();
        if ($jawaban->tugas->auto_grade) {
            ProcessAutoGrading::dispatch($jawaban->id);
        }
        return redirect()->route('mahasiswa.tugas.show', $jawaban->tugas)
            ->with('success', 'Jawaban berhasil disubmit. ' .
                ($jawaban->tugas->auto_grade ? 'Penilaian otomatis sedang diproses.' : 'Menunggu penilaian dari dosen.'));
    }
    
    /**
     * Auto submit when time is up
     */
    private function autoSubmit(JawabanMahasiswa $jawaban)
    {
        if ($jawaban->status === 'draft') {
            $jawaban->update([
                'waktu_selesai' => Carbon::now(),
                'status' => 'submitted'
            ]);
            
            // Trigger auto grading jika diaktifkan
            if ($jawaban->tugas->auto_grade) {
                ProcessAutoGrading::dispatch($jawaban->id);
            }
        }
        
        return redirect()->route('mahasiswa.tugas.show', $jawaban->tugas)
            ->with('warning', 'Waktu ujian habis. Jawaban Anda telah disubmit otomatis.');
    }
    
    /**
     * Get remaining time (AJAX)
     */
    public function getRemainingTime(JawabanMahasiswa $jawaban)
    {
        $mahasiswa = auth()->user();
        
        // Check ownership
        if ($jawaban->mahasiswa_id !== $mahasiswa->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if ($jawaban->status !== 'draft') {
            return response()->json(['error' => 'Jawaban sudah disubmit'], 400);
        }
        
        $waktuMulai = $jawaban->waktu_mulai;
        $durasiMenit = $jawaban->tugas->durasi_menit;
        $deadline = $jawaban->tugas->deadline;
        
        // Waktu selesai berdasarkan durasi atau deadline (yang lebih dulu)
        $waktuSelesaiDurasi = $waktuMulai->copy()->addMinutes($durasiMenit);
        $waktuSelesai = $waktuSelesaiDurasi->lt($deadline) ? $waktuSelesaiDurasi : $deadline;
        
        $sisaWaktu = Carbon::now()->diffInSeconds($waktuSelesai, false);
        
        return response()->json([
            'remaining_seconds' => max(0, $sisaWaktu),
            'is_expired' => $sisaWaktu <= 0
        ]);
    }
    
    /**
     * Daftar ujian mahasiswa
     */
    public function index(Request $request)
    {
        $mahasiswa = auth()->user();
        // Ambil mata kuliah yang diambil mahasiswa
        $mataKuliahIds = $mahasiswa->enrollments()->active()->pluck('mata_kuliah_id');
        // Ambil daftar ujian (misal: tugas yang bertipe ujian, atau model Ujian jika ada)
        $ujian = \App\Models\Tugas::whereIn('mata_kuliah_id', $mataKuliahIds)
            ->with(['mataKuliah', 'dosen'])
            ->orderByDesc('deadline')
            ->paginate(10);
        // Untuk setiap ujian, ambil jawaban mahasiswa jika ada
        foreach ($ujian as $u) {
            $u->jawaban = $mahasiswa->jawabanMahasiswa()->where('tugas_id', $u->id)->first();
        }
        return view('mahasiswa.ujian.index', compact('ujian'));
    }
}
