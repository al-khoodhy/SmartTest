<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    protected $fillable = [
        'nama_kelas',
        'mata_kuliah_id',
        'dosen_id',
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'kelas_id');
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'kelas_id');
    }
} 