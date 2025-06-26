<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;
    
    protected $table = 'penilaian';
    
    protected $fillable = [
        'jawaban_id',
        'nilai_ai',
        'nilai_manual',
        'nilai_final',
        'feedback_ai',
        'feedback_manual',
        'detail_penilaian_ai',
        'status_penilaian',
        'graded_by',
        'graded_at'
    ];
    
    protected $casts = [
        'nilai_ai' => 'decimal:2',
        'nilai_manual' => 'decimal:2',
        'nilai_final' => 'decimal:2',
        'detail_penilaian_ai' => 'array',
        'graded_at' => 'datetime',
    ];
    
    // Relasi dengan JawabanMahasiswa
    public function jawaban()
    {
        return $this->belongsTo(JawabanMahasiswa::class, 'jawaban_id');
    }
    
    // Relasi dengan User (Grader)
    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
    
    // Scope untuk penilaian yang sudah selesai
    public function scopeCompleted($query)
    {
        return $query->where('status_penilaian', 'final');
    }
    
    // Scope untuk penilaian AI
    public function scopeAiGraded($query)
    {
        return $query->where('status_penilaian', 'ai_graded');
    }
    
    // Get nilai akhir
    public function getNilaiAkhirAttribute()
    {
        return $this->nilai_final ?? $this->nilai_manual ?? $this->nilai_ai;
    }
    
    // Get feedback gabungan
    public function getFeedbackGabunganAttribute()
    {
        $feedback = [];
        
        if ($this->feedback_ai) {
            $feedback[] = "Feedback AI: " . $this->feedback_ai;
        }
        
        if ($this->feedback_manual) {
            $feedback[] = "Feedback Dosen: " . $this->feedback_manual;
        }
        
        return implode("\n\n", $feedback);
    }
}
