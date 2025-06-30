@extends('layouts.app')

@section('content')
<style>
    .feedback-content {
        line-height: 1.6;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .feedback-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .feedback-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .feedback-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .feedback-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    @media (max-width: 768px) {
        .table-responsive table {
            font-size: 0.85em;
        }
        
        .feedback-content {
            max-height: 200px !important;
        }
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Nilai & Feedback</h4>
                    <a href="{{ route('mahasiswa.nilai.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Nilai
                    </a>
                </div>
                <div class="card-body">
                    <!-- Informasi Tugas -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Informasi Tugas</h5>
                    <dl class="row">
                        <dt class="col-sm-4">Mata Kuliah</dt>
                                <dd class="col-sm-8">{{ $jawaban->tugas->mataKuliah->nama_mk ?? '-' }}</dd>
                                <dt class="col-sm-4">Kelas</dt>
                                <dd class="col-sm-8">{{ $jawaban->tugas->kelas->nama_kelas ?? '-' }}</dd>
                                <dt class="col-sm-4">Judul Tugas</dt>
                                <dd class="col-sm-8">{{ $jawaban->tugas->judul ?? '-' }}</dd>
                                <dt class="col-sm-4">Nilai Maksimal</dt>
                                <dd class="col-sm-8">{{ $jawaban->tugas->nilai_maksimal ?? 100 }}</dd>
                                <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @php
                                $statusBadge = '';
                                if($jawaban->status === 'draft') {
                                    $statusBadge = '<span class="badge bg-warning text-dark">Sedang Dikerjakan</span>';
                                } elseif($jawaban->penilaian && $jawaban->penilaian->status_penilaian == 'ai_graded') {
                                    $statusBadge = '<span class="badge bg-info text-dark">AI Graded</span>';
                                } elseif(($jawaban->penilaian && $jawaban->penilaian->status_penilaian == 'final') || $jawaban->status === 'graded') {
                                    $statusBadge = '<span class="badge bg-success">Sudah Dinilai</span>';
                                } elseif($jawaban->status === 'submitted') {
                                    $statusBadge = '<span class="badge bg-info text-dark">Menunggu Penilaian</span>';
                                } else {
                                    $statusBadge = '<span class="badge bg-secondary">' . ucfirst($jawaban->status) . '</span>';
                                }
                            @endphp
                            {!! $statusBadge !!}
                        </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h5>Informasi Penilaian</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Tanggal Submit</dt>
                                <dd class="col-sm-8">{{ $jawaban->waktu_selesai ? $jawaban->waktu_selesai->format('d/m/Y H:i') : '-' }}</dd>
                                <dt class="col-sm-4">Durasi Pengerjaan</dt>
                                <dd class="col-sm-8">{{ $jawaban->durasi_format ?? '-' }}</dd>
                                <dt class="col-sm-4">Total Soal</dt>
                                <dd class="col-sm-8">{{ $jawaban->jawabanSoal->count() }} soal</dd>
                                <dt class="col-sm-4">Total Bobot</dt>
                                <dd class="col-sm-8">{{ $jawaban->jawabanSoal->sum(function($js) { return $js->soal->bobot ?? 1; }) }} poin</dd>
                                @if($jawaban->status === 'graded')
                                    <dt class="col-sm-4">Progress Penilaian</dt>
                        <dd class="col-sm-8">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $jawaban->grading_progress }}%">
                                                {{ $jawaban->grading_progress }}%
                                            </div>
                                        </div>
                                    </dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Nilai Akhir -->
                    @if($jawaban->status === 'graded')
                        <div class="alert alert-success mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h4 class="mb-0">Nilai Akhir: <span class="badge bg-success fs-4">{{ $jawaban->nilai_akhir }}</span></h4>
                                    <small class="text-muted">
                                        @if($jawaban->is_all_graded)
                                            ✅ Semua soal sudah dinilai
                                        @else
                                            ⚠️ Progress: {{ $jawaban->grading_progress }}% ({{ $jawaban->jawabanSoal->filter(function($js) { return $js->penilaian && in_array($js->penilaian->status_penilaian, ['final', 'ai_graded']); })->count() }}/{{ $jawaban->jawabanSoal->count() }} soal)
                                        @endif
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    @if($jawaban->nilai_akhir >= 75)
                                        <span class="badge bg-success fs-6">Sangat Baik</span>
                                    @elseif($jawaban->nilai_akhir >= 60)
                                        <span class="badge bg-warning fs-6">Baik</span>
                                    @else
                                        <span class="badge bg-danger fs-6">Perlu Perbaikan</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Detail Penilaian Per Soal -->
                    <h5>Detail Penilaian Per Soal</h5>
                    <div class="mb-4">
                        @forelse($jawaban->jawabanSoal as $index => $jawabanSoal)
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong>Soal #{{ $index + 1 }}</strong>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table mb-0 table-bordered">
                                        <tbody>
                                            <tr>
                                                <th width="20%">No</th>
                                                <td>{{ $index + 1 }}</td>
                                            </tr>
                                            <tr>
                                                <th>Soal</th>
                                                <td>{{ $jawabanSoal->soal->pertanyaan ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Jawaban Mahasiswa</th>
                                                <td><div class="border p-2 bg-light" style="max-height: 150px; overflow-y: auto;">{!! nl2br(e($jawabanSoal->jawaban)) !!}</div></td>
                                            </tr>
                                            <tr>
                                                <th>Bobot</th>
                                                <td>{{ $jawabanSoal->soal->bobot ?? 1 }}</td>
                                            </tr>
                                            <tr>
                                                <th>Nilai</th>
                                                <td>
                                                    @if($jawabanSoal->penilaian)
                                                        <span class="badge bg-primary">{{ $jawabanSoal->penilaian->nilai_final ?? $jawabanSoal->penilaian->nilai_manual ?? $jawabanSoal->penilaian->nilai_ai ?? 0 }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    @if($jawabanSoal->penilaian)
                                                        @if($jawabanSoal->penilaian->status_penilaian == 'final')
                                                            <span class="badge bg-success">Manual</span>
                                                        @elseif($jawabanSoal->penilaian->status_penilaian == 'ai_graded')
                                                            <span class="badge bg-info">AI</span>
                                                        @else
                                                            <span class="badge bg-warning">{{ ucfirst($jawabanSoal->penilaian->status_penilaian) }}</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">Belum Dinilai</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Feedback</th>
                                                <td>
                                                    @if($jawabanSoal->penilaian)
                                                        @if($jawabanSoal->penilaian->feedback_manual)
                                                            <div class="mb-2">
                                                                <small class="text-success fw-bold">
                                                                    <i class="bi bi-person-check"></i> Feedback Dosen:
                                                                </small>
                                                                <div class="mt-1 p-2 bg-light border-start border-success border-3">
                                                                    {!! nl2br(e($jawabanSoal->penilaian->feedback_manual)) !!}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($jawabanSoal->penilaian->feedback_ai)
                                                            <div>
                                                                <small class="text-primary fw-bold">
                                                                    <i class="bi bi-robot"></i> Feedback AI:
                                                                </small>
                                                                <div class="mt-1 p-2 bg-light border-start border-primary border-3">
                                                                    {!! nl2br(e($jawabanSoal->penilaian->feedback_ai)) !!}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if(!$jawabanSoal->penilaian->feedback_manual && !$jawabanSoal->penilaian->feedback_ai)
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning">Tidak ada soal yang tersedia.</div>
                        @endforelse
                    </div>

                    <!-- Feedback Keseluruhan -->
                    @if($jawaban->penilaian)
                        <h5>Feedback Keseluruhan</h5>
                        <div class="row mb-4">
                            @if($jawaban->penilaian->feedback_ai)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-primary h-100">
                                        <div class="card-header bg-primary text-white">
                                            <i class="bi bi-robot"></i> Feedback AI
                                        </div>
                                        <div class="card-body">
                                            <div class="feedback-content" style="max-height: 300px; overflow-y: auto;">
                                                {!! nl2br(e($jawaban->penilaian->feedback_ai)) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($jawaban->penilaian->feedback_manual)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-success h-100">
                                        <div class="card-header bg-success text-white">
                                            <i class="bi bi-person-check"></i> Feedback Dosen
                                        </div>
                                        <div class="card-body">
                                            <div class="feedback-content" style="max-height: 300px; overflow-y: auto;">
                                                {!! nl2br(e($jawaban->penilaian->feedback_manual)) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Tombol Aksi -->
                    <div class="text-center mt-4">
                        <a href="{{ route('mahasiswa.nilai.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Nilai
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 