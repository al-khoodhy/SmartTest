<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TugasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:dosen']);
    }
    
    /**
     * Display a listing of tugas
     */
    public function index(Request $request)
    {
        $dosen = auth()->user();
        // Get tugas yang dosen ampu (melalui mata kuliah)
        $query = \App\Models\Tugas::whereHas('mataKuliah', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->with('mataKuliah');
        // Filter berdasarkan mata kuliah
        if ($request->mata_kuliah_id) {
            $query->where('mata_kuliah_id', $request->mata_kuliah_id);
        }
        // Filter berdasarkan status
        if ($request->status) {
            switch ($request->status) {
                case 'active':
                    $query->active()->available();
                    break;
                case 'expired':
                    $query->where('deadline', '<', \Carbon\Carbon::now());
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }
        $tugas = $query->latest()->paginate(10);
        // Get mata kuliah untuk filter dropdown
        $mataKuliah = \App\Models\MataKuliah::whereHas('dosen', function($q) use ($dosen) {
            $q->where('users.id', $dosen->id);
        })->where('is_active', true)->get();
        return view('dosen.tugas.index', compact('tugas', 'mataKuliah'));
    }
    
    /**
     * Show the form for creating a new tugas
     */
    public function create()
    {
        $dosen = auth()->user();
        $mataKuliah = \App\Models\MataKuliah::whereHas('dosen', function($q) use ($dosen) {
            $q->where('users.id', $dosen->id);
        })->where('is_active', true)->get();
        $kelasList = \App\Models\Kelas::where('dosen_id', $dosen->id)->get();
        return view('dosen.tugas.create', compact('mataKuliah', 'kelasList'));
    }
    
    /**
     * Store a newly created tugas
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'kelas_id' => 'required|exists:kelas,id',
            'deadline' => 'required|date|after:now',
            'durasi_menit' => 'required|integer|min:30|max:480',
            'nilai_maksimal' => 'required|integer|min:1|max:100',
            'rubrik_penilaian' => 'nullable|string',
            'auto_grade' => 'boolean',
            'soal.*.pertanyaan' => 'required|string',
            'soal.*.bobot' => 'required|numeric|min:0.01',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $dosen = auth()->user();
        $tugas = \App\Models\Tugas::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'mata_kuliah_id' => $request->mata_kuliah_id,
            'kelas_id' => $request->kelas_id,
            'dosen_id' => $dosen->id,
            'deadline' => $request->deadline,
            'durasi_menit' => $request->durasi_menit,
            'nilai_maksimal' => $request->nilai_maksimal,
            'rubrik_penilaian' => $request->rubrik_penilaian,
            'auto_grade' => $request->has('auto_grade'),
            'is_active' => true
        ]);
        // Simpan soal
        foreach ($request->soal as $soal) {
            $tugas->soal()->create([
                'pertanyaan' => $soal['pertanyaan'],
                'bobot' => $soal['bobot'],
            ]);
        }
        return redirect()->route('dosen.tugas.show', $tugas)->with('success', 'Tugas berhasil dibuat.');
    }
    
    /**
     * Display the specified tugas
     */
    public function show(\App\Models\Tugas $tugas)
    {
        $tugas->load('mataKuliah');
        \Log::info('TugasController@show', [
            'user_id' => auth()->id(),
            'tugas_id' => $tugas->id,
            'mata_kuliah_id' => $tugas->mata_kuliah_id,
            'dosen_id' => $tugas->mataKuliah ? $tugas->mataKuliah->dosen_id : null,
        ]);
        $this->authorize('view', $tugas);
        $tugas->load(['mataKuliah', 'jawabanMahasiswa.mahasiswa', 'jawabanMahasiswa.penilaian']);
        
        // Statistik tugas
        $totalMahasiswa = $tugas->mataKuliah->enrollments()->active()->count();
        $sudahMengerjakan = $tugas->jawabanMahasiswa()->count();
        $sudahSubmit = $tugas->jawabanMahasiswa()->where('status', 'submitted')->count();
        $sudahDinilai = $tugas->jawabanMahasiswa()->where('status', 'graded')->count();
        
        return view('dosen.tugas.show', compact('tugas', 'totalMahasiswa', 'sudahMengerjakan', 'sudahSubmit', 'sudahDinilai'));
    }
    
    /**
     * Show the form for editing the specified tugas
     */
    public function edit(\App\Models\Tugas $tugas)
    {
        $tugas->load('mataKuliah');
        \Log::info('TugasController@edit', [
            'user_id' => auth()->id(),
            'tugas_id' => $tugas->id,
            'mata_kuliah_id' => $tugas->mata_kuliah_id,
            'dosen_id' => $tugas->mataKuliah ? $tugas->mataKuliah->dosen_id : null,
        ]);
        $this->authorize('update', $tugas);
        $dosen = auth()->user();
        $mataKuliah = \App\Models\MataKuliah::whereHas('dosen', function($q) use ($dosen) {
            $q->where('users.id', $dosen->id);
        })->where('is_active', true)->get();
        $kelasList = \App\Models\Kelas::where('dosen_id', $dosen->id)->get();
        return view('dosen.tugas.edit', compact('tugas', 'mataKuliah', 'kelasList'));
    }
    
    /**
     * Update the specified tugas
     */
    public function update(Request $request, \App\Models\Tugas $tugas)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'kelas_id' => 'required|exists:kelas,id',
            'deadline' => 'required|date',
            'durasi_menit' => 'required|integer|min:30|max:480',
            'nilai_maksimal' => 'required|integer|min:1|max:100',
            'rubrik_penilaian' => 'nullable|string',
            'auto_grade' => 'boolean',
            'is_active' => 'boolean',
            'soal.*.pertanyaan' => 'required|string',
            'soal.*.bobot' => 'required|numeric|min:0.01',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $tugas->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'mata_kuliah_id' => $request->mata_kuliah_id,
            'kelas_id' => $request->kelas_id,
            'deadline' => $request->deadline,
            'durasi_menit' => $request->durasi_menit,
            'nilai_maksimal' => $request->nilai_maksimal,
            'rubrik_penilaian' => $request->rubrik_penilaian,
            'auto_grade' => $request->has('auto_grade'),
            'is_active' => $request->has('is_active')
        ]);
        // Update soal: hapus semua lalu insert ulang (atau bisa dioptimasi)
        $tugas->soal()->delete();
        foreach ($request->soal as $soal) {
            $tugas->soal()->create([
                'pertanyaan' => $soal['pertanyaan'],
                'bobot' => $soal['bobot'],
            ]);
        }
        return redirect()->route('dosen.tugas.show', $tugas)->with('success', 'Tugas berhasil diupdate.');
    }
    
    /**
     * Remove the specified tugas
     */
    public function destroy(\App\Models\Tugas $tugas)
    {
        $tugas->load('mataKuliah');
        \Log::info('TugasController@destroy', [
            'user_id' => auth()->id(),
            'tugas_id' => $tugas->id,
            'mata_kuliah_id' => $tugas->mata_kuliah_id,
            'dosen_id' => $tugas->mataKuliah ? $tugas->mataKuliah->dosen_id : null,
        ]);
        $this->authorize('delete', $tugas);
        $tugas->delete();
        return redirect()->route('dosen.tugas.index')->with('success', 'Tugas berhasil dihapus.');
    }
    
    /**
     * Toggle tugas status
     */
    public function toggleStatus(\App\Models\Tugas $tugas)
    {
        $tugas->load('mataKuliah');
        \Log::info('TugasController@toggleStatus', [
            'user_id' => auth()->id(),
            'tugas_id' => $tugas->id,
            'mata_kuliah_id' => $tugas->mata_kuliah_id,
            'dosen_id' => $tugas->mataKuliah ? $tugas->mataKuliah->dosen_id : null,
        ]);
        $this->authorize('update', $tugas);
        $tugas->update(['is_active' => !$tugas->is_active]);
        $status = $tugas->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Tugas berhasil {$status}.");
    }
}
