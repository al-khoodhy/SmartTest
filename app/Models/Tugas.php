<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Tugas extends Model
{
    use HasFactory;
    
    protected $table = 'tugas';
    
    protected $fillable = [
        'judul',
        'deskripsi',
        'rubrik_penilaian',
        'kelas_id',
        'dosen_id',
        'deadline',
        'durasi_menit',
        'nilai_maksimal',
        'is_active',
        'auto_grade'
    ];
    
    protected $casts = [
        'deadline' => 'datetime',
        'is_active' => 'boolean',
        'auto_grade' => 'boolean',
    ];
    
    // Relasi dengan JawabanMahasiswa
    public function jawabanMahasiswa()
    {
        return $this->hasMany(JawabanMahasiswa::class, 'tugas_id');
    }
    
    // Relasi ke kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    
    // Accessor agar $tugas->mataKuliah langsung instance MataKuliah
    public function getMataKuliahAttribute()
    {
        return $this->kelas && $this->kelas->mataKuliah ? $this->kelas->mataKuliah : null;
    }
    
    // Relasi ke Soal
    public function soal()
    {
        return $this->hasMany(\App\Models\Soal::class, 'tugas_id');
    }
    
    // Scope untuk tugas aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Scope untuk tugas yang belum deadline
    public function scopeAvailable($query)
    {
        return $query->where('deadline', '>', Carbon::now());
    }
    
    // Check apakah tugas sudah deadline
    public function isExpired()
    {
        return $this->deadline < Carbon::now();
    }
    
    // Get status tugas
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        if ($this->isExpired()) {
            return 'expired';
        }
        
        return 'active';
    }
    
    // Relasi langsung ke dosen yang membuat tugas
    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
    
    // Relasi ke dosen via kelas (many-to-many) - untuk kompatibilitas
    public function dosenKelas()
    {
        return $this->hasManyThrough(
            \App\Models\User::class,
            \App\Models\Kelas::class,
            'id', // Foreign key on kelas table...
            'id', // Foreign key on users table...
            'kelas_id', // Local key on tugas table...
            'id' // Local key on kelas table...
        )->whereHas('dosen', function($q) {
            $q->where('dosen_kelas.kelas_id', DB::raw('kelas.id'));
        });
    }
}
