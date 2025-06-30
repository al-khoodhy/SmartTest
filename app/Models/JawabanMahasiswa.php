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
    
    // Nilai akhir total (rata-rata tertimbang dari nilai_final PenilaianSoal)
    public function getNilaiAkhirAttribute()
    {
        // Hitung nilai akhir berdasarkan PenilaianSoal (per soal)
        $totalBobot = $this->jawabanSoal->sum(function($js) { 
            return $js->soal->bobot ?? 1; // Default bobot 1 jika null
        });
        
        if ($totalBobot == 0) return 0;
        
        $totalNilai = $this->jawabanSoal->sum(function($js) {
            $penilaian = $js->penilaian;
            if (!$penilaian) return 0;
            
            // Ambil nilai final dari PenilaianSoal
            $nilai = $penilaian->nilai_final ?? $penilaian->nilai_manual ?? $penilaian->nilai_ai ?? 0;
            $bobot = $js->soal->bobot ?? 1;
            
            return $nilai * $bobot;
        });
        
        $nilaiAkhir = round($totalNilai / $totalBobot, 2);
        
        // Pastikan nilai akhir tidak melebihi nilai maksimal tugas
        $nilaiMaksimal = $this->tugas->nilai_maksimal ?? 100;
        return min($nilaiAkhir, $nilaiMaksimal);
    }
    
    // Cek apakah semua soal sudah dinilai
    public function getIsAllGradedAttribute()
    {
        return $this->jawabanSoal->every(function($js) {
            $penilaian = $js->penilaian;
            return $penilaian && in_array($penilaian->status_penilaian, ['final', 'ai_graded']);
        });
    }
    
    // Hitung persentase kelengkapan penilaian
    public function getGradingProgressAttribute()
    {
        $totalSoal = $this->jawabanSoal->count();
        if ($totalSoal == 0) return 0;
        
        $gradedSoal = $this->jawabanSoal->filter(function($js) {
            $penilaian = $js->penilaian;
            return $penilaian && in_array($penilaian->status_penilaian, ['final', 'ai_graded']);
        })->count();
        
        return round(($gradedSoal / $totalSoal) * 100, 2);
    }
    
    // Get nilai AI dari PenilaianSoal (rata-rata tertimbang)
    public function getNilaiAiAttribute()
    {
        $totalBobot = $this->jawabanSoal->sum(function($js) { 
            return $js->soal->bobot ?? 1;
        });
        
        if ($totalBobot == 0) return 0;
        
        $totalNilai = $this->jawabanSoal->sum(function($js) {
            $penilaian = $js->penilaian;
            if (!$penilaian || $penilaian->nilai_ai === null) return 0;
            
            $bobot = $js->soal->bobot ?? 1;
            return $penilaian->nilai_ai * $bobot;
        });
        
        $nilaiAi = round($totalNilai / $totalBobot, 2);
        
        // Pastikan nilai tidak melebihi nilai maksimal tugas
        $nilaiMaksimal = $this->tugas->nilai_maksimal ?? 100;
        return min($nilaiAi, $nilaiMaksimal);
    }
    
    // Get nilai manual dari PenilaianSoal (rata-rata tertimbang)
    public function getNilaiManualAttribute()
    {
        $totalBobot = $this->jawabanSoal->sum(function($js) { 
            return $js->soal->bobot ?? 1;
        });
        
        if ($totalBobot == 0) return 0;
        
        $totalNilai = $this->jawabanSoal->sum(function($js) {
            $penilaian = $js->penilaian;
            if (!$penilaian || $penilaian->nilai_manual === null) return 0;
            
            $bobot = $js->soal->bobot ?? 1;
            return $penilaian->nilai_manual * $bobot;
        });
        
        $nilaiManual = round($totalNilai / $totalBobot, 2);
        
        // Pastikan nilai tidak melebihi nilai maksimal tugas
        $nilaiMaksimal = $this->tugas->nilai_maksimal ?? 100;
        return min($nilaiManual, $nilaiMaksimal);
    }
}
