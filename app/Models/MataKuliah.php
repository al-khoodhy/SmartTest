<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;
    
    protected $table = 'mata_kuliah';
    
    protected $fillable = [
        'kode_mk',
        'nama_mk',
        'deskripsi',
        'sks',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    // Relasi dengan User (Dosen)
    public function dosen()
    {
        return $this->belongsToMany(User::class, 'mata_kuliah_user', 'mata_kuliah_id', 'user_id');
    }
    
    // Relasi ke kelas
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'mata_kuliah_id');
    }
    
    // Scope untuk mata kuliah aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
