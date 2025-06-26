<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanMahasiswa extends Model
{
    use HasFactory;
    
    protected $table = 'jawaban_mahasiswa';
    
    protected $fillable = [
        'tugas_id',
        'mahasiswa_id',
        'jawaban',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'durasi_detik'
    ];
    
    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];
    
    // Relasi dengan Tugas
    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }
    
    // Relasi dengan User (Mahasiswa)
    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }
    
    // Relasi dengan Penilaian
    public function penilaian()
    {
        return $this->hasOne(Penilaian::class, 'jawaban_id');
    }
    
    // Relasi ke jawaban per soal
    public function jawabanSoal()
    {
        return $this->hasMany(\App\Models\JawabanSoalMahasiswa::class, 'jawaban_mahasiswa_id');
    }
    
    // Scope untuk jawaban yang sudah disubmit
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }
    
    // Scope untuk jawaban yang sudah dinilai
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }
    
    // Get durasi dalam format yang readable
    public function getDurasiFormatAttribute()
    {
        if (!$this->durasi_detik) {
            return 'Belum selesai';
        }
        
        $hours = floor($this->durasi_detik / 3600);
        $minutes = floor(($this->durasi_detik % 3600) / 60);
        $seconds = $this->durasi_detik % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    // Nilai akhir total (sum nilai_final per soal dikali bobot, dibagi total bobot)
    public function getNilaiAkhirAttribute()
    {
        $totalBobot = $this->jawabanSoal->sum(function($js) { return $js->soal->bobot; });
        if ($totalBobot == 0) return 0;
        $total = $this->jawabanSoal->sum(function($js) {
            $nilai = optional($js->penilaian)->nilai_final;
            return ($nilai ?? 0) * $js->soal->bobot;
        });
        return round($total / $totalBobot, 2);
    }
}
