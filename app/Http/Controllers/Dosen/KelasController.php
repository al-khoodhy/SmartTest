<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:2']);
    }
    
    /**
     * Display a listing of kelas
     */
    public function index()
    {
        $dosen = auth()->user();
        $kelas = $dosen->kelasAsDosen()->with(['mataKuliah', 'enrollments'])->get();
        
        return view('dosen.kelas.index', compact('kelas'));
    }
    
    /**
     * Show the form for creating a new kelas
     */
    public function create()
    {
        $dosen = auth()->user();
        $mataKuliah = $dosen->mataKuliahDiampu()->where('is_active', true)->get();
        
        return view('dosen.kelas.create', compact('mataKuliah'));
    }
    
    /**
     * Store a newly created kelas
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string|max:255',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dosen = auth()->user();
        
        // Verify that the dosen teaches this mata kuliah
        if (!$dosen->mataKuliahDiampu()->where('mata_kuliah.id', $request->mata_kuliah_id)->exists()) {
            return redirect()->back()
                ->withErrors(['mata_kuliah_id' => 'Anda tidak mengajar mata kuliah ini.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $dosen) {
            $kelas = Kelas::create([
                'nama_kelas' => $request->nama_kelas,
                'mata_kuliah_id' => $request->mata_kuliah_id,
            ]);

            // Attach kelas to the dosen
            $dosen->kelasAsDosen()->attach($kelas->id);
        });

        return redirect()->route('dosen.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }
    
    /**
     * Display the specified kelas
     */
    public function show($id)
    {
        $dosen = auth()->user();
        $kelas = Kelas::findOrFail($id);
        
        // Check if dosen teaches this kelas
        if (!$dosen->kelasAsDosen()->where('kelas.id', $kelas->id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
        
        $enrollments = $kelas->enrollments()->with('mahasiswa')->get();
        $tugas = $kelas->tugas()->latest()->get();
        
        return view('dosen.kelas.show', compact('kelas', 'enrollments', 'tugas'));
    }
    
    /**
     * Show the form for editing the specified kelas
     */
    public function edit($id)
    {
        $dosen = auth()->user();
        $kelas = Kelas::findOrFail($id);
        
        // Check if dosen teaches this kelas
        if (!$dosen->kelasAsDosen()->where('kelas.id', $kelas->id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
        
        $mataKuliah = $dosen->mataKuliahDiampu()->where('is_active', true)->get();
        
        return view('dosen.kelas.edit', compact('kelas', 'mataKuliah'));
    }
    
    /**
     * Update the specified kelas
     */
    public function update(Request $request, $id)
    {
        $dosen = auth()->user();
        $kelas = Kelas::findOrFail($id);
        
        // Check if dosen teaches this kelas
        if (!$dosen->kelasAsDosen()->where('kelas.id', $kelas->id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
        
        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string|max:255',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify that the dosen teaches this mata kuliah
        if (!$dosen->mataKuliahDiampu()->where('mata_kuliah.id', $request->mata_kuliah_id)->exists()) {
            return redirect()->back()
                ->withErrors(['mata_kuliah_id' => 'Anda tidak mengajar mata kuliah ini.'])
                ->withInput();
        }

        $kelas->update([
            'nama_kelas' => $request->nama_kelas,
            'mata_kuliah_id' => $request->mata_kuliah_id,
        ]);

        return redirect()->route('dosen.kelas.show', $kelas)
            ->with('success', 'Kelas berhasil diupdate.');
    }
    
    /**
     * Remove the specified kelas
     */
    public function destroy($id)
    {
        $dosen = auth()->user();
        $kelas = Kelas::findOrFail($id);
        
        // Check if dosen teaches this kelas
        if (!$dosen->kelasAsDosen()->where('kelas.id', $kelas->id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
        
        // Check if kelas has active enrollments
        if ($kelas->enrollments()->exists()) {
            return redirect()->back()
                ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki mahasiswa yang terdaftar.');
        }
        
        // Check if kelas has active tugas
        if ($kelas->tugas()->exists()) {
            return redirect()->back()
                ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki tugas aktif.');
        }
        
        // Detach from dosen
        $dosen->kelasAsDosen()->detach($kelas->id);
        
        // Delete kelas
        $kelas->delete();
        
        return redirect()->route('dosen.kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
