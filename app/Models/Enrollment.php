<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'mahasiswa_id',
        'kelas_id',
        'status',
        'enrolled_at',
        'tanggal_daftar',
    ];
    
    protected $casts = [
        'enrolled_at' => 'date',
    ];
    
    // Relasi dengan User (Mahasiswa)
    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
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
