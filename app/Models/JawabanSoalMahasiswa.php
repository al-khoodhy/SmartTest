<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanSoalMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'jawaban_soal_mahasiswa';

    protected $fillable = [
        'jawaban_mahasiswa_id',
        'soal_id',
        'jawaban',
        'waktu_mulai',
        'waktu_selesai',
        'status',
    ];

    public function jawabanMahasiswa()
    {
        return $this->belongsTo(JawabanMahasiswa::class, 'jawaban_mahasiswa_id');
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }

    public function penilaian()
    {
        return $this->hasOne(\App\Models\PenilaianSoal::class, 'jawaban_soal_id');
    }
} 