<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MataKuliahController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:2']);
    }
    
    /**
     * Display a listing of mata kuliah
     */
    public function index()
    {
        $dosen = auth()->user();
        $mataKuliah = $dosen->mataKuliahDiampu()->with('kelas')->get();
        
        return view('dosen.mata-kuliah.index', compact('mataKuliah'));
    }
    
    /**
     * Show the form for creating a new mata kuliah
     */
    public function create()
    {
        return view('dosen.mata-kuliah.create');
    }
    
    /**
     * Store a newly created mata kuliah
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_mk' => 'required|string|max:50|unique:mata_kuliah,kode_mk',
            'nama_mk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'sks' => 'required|integer|min:1|max:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::transaction(function () use ($request) {
            $mataKuliah = MataKuliah::create([
                'kode_mk' => $request->kode_mk,
                'nama_mk' => $request->nama_mk,
                'deskripsi' => $request->deskripsi,
                'sks' => $request->sks,
                'is_active' => true,
            ]);

            // Attach mata kuliah to the dosen
            auth()->user()->mataKuliahDiampu()->attach($mataKuliah->id);
        });

        return redirect()->route('dosen.mata-kuliah.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }
    
    /**
     * Display the specified mata kuliah
     */
    public function show(MataKuliah $mataKuliah)
    {
        $dosen = auth()->user();
        
        // Check if dosen teaches this mata kuliah
        if (!$dosen->mataKuliahDiampu()->where('mata_kuliah.id', $mataKuliah->id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke mata kuliah ini.');
        }
        
        $kelas = $mataKuliah->kelas()->with('dosen')->get();
        $tugas = $mataKuliah->kelas()->with('tugas')->get()->pluck('tugas')->flatten();
        
        return view('dosen.mata-kuliah.show', compact('mataKuliah', 'kelas', 'tugas'));
    }
    
    /**
     * Show the form for editing the specified mata kuliah
     */
    public function edit(MataKuliah $mataKuliah)
    {
        $dosen = auth()->user();
        
        // Check if dosen teaches this mata kuliah
        if (!$dosen->mataKuliahDiampu()->where('mata_kuliah.id', $mataKuliah->id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke mata kuliah ini.');
        }
        
        return view('dosen.mata-kuliah.edit', compact('mataKuliah'));
    }
    
    /**
     * Update the specified mata kuliah
     */
    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $dosen = auth()->user();
        
        // Check if dosen teaches this mata kuliah
        if (!$dosen->mataKuliahDiampu()->where('mata_kuliah.id', $mataKuliah->id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke mata kuliah ini.');
        }
        
        $validator = Validator::make($request->all(), [
            'kode_mk' => 'required|string|max:50|unique:mata_kuliah,kode_mk,' . $mataKuliah->id,
            'nama_mk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'sks' => 'required|integer|min:1|max:6',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $mataKuliah->update([
            'kode_mk' => $request->kode_mk,
            'nama_mk' => $request->nama_mk,
            'deskripsi' => $request->deskripsi,
            'sks' => $request->sks,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('dosen.mata-kuliah.show', $mataKuliah)
            ->with('success', 'Mata kuliah berhasil diupdate.');
    }
    
    /**
     * Remove the specified mata kuliah
     */
    public function destroy(MataKuliah $mataKuliah)
    {
        $dosen = auth()->user();
        
        // Check if dosen teaches this mata kuliah
        if (!$dosen->mataKuliahDiampu()->where('mata_kuliah.id', $mataKuliah->id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke mata kuliah ini.');
        }
        
        // Check if mata kuliah has active classes
        if ($mataKuliah->kelas()->exists()) {
            return redirect()->back()
                ->with('error', 'Mata kuliah tidak dapat dihapus karena masih memiliki kelas aktif.');
        }
        
        // Detach from dosen
        $dosen->mataKuliahDiampu()->detach($mataKuliah->id);
        
        // Delete mata kuliah
        $mataKuliah->delete();
        
        return redirect()->route('dosen.mata-kuliah.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
