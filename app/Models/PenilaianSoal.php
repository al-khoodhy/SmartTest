<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianSoal extends Model
{
    use HasFactory;

    protected $table = 'penilaian_soal';

    protected $fillable = [
        'jawaban_soal_id',
        'nilai_ai',
        'nilai_manual',
        'nilai_final',
        'feedback_ai',
        'feedback_manual',
        'status_penilaian',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'nilai_ai' => 'decimal:2',
        'nilai_manual' => 'decimal:2',
        'nilai_final' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function jawabanSoal()
    {
        return $this->belongsTo(JawabanSoalMahasiswa::class, 'jawaban_soal_id');
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
} 