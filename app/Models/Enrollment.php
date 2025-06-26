<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'status',
        'enrolled_at'
    ];
    
    protected $casts = [
        'enrolled_at' => 'date',
    ];
    
    // Relasi dengan User (Mahasiswa)
    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }
    
    // Relasi dengan MataKuliah
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
    
    // Relasi ke kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    
    // Scope untuk enrollment aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
