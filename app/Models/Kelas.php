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
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function dosen()
    {
        return $this->belongsToMany(User::class, 'dosen_kelas', 'kelas_id', 'dosen_id')
                    ->where('role_id', 2); // Only users with role_id 2 (dosen)
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