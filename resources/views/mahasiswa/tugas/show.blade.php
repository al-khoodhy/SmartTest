@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Tugas</h4>
                    <a href="{{ route('mahasiswa.tugas.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Judul Tugas</dt>
                        <dd class="col-sm-8">{{ $tugas->judul }}</dd>
                        <dt class="col-sm-4">Mata Kuliah</dt>
                        <dd class="col-sm-8">{{ $tugas->mataKuliah->nama_mk }}</dd>
                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $tugas->deskripsi }}</dd>
                        <dt class="col-sm-4">Jumlah Soal</dt>
                        <dd class="col-sm-8">{{ $tugas->soal->count() }} soal</dd>
                        <dt class="col-sm-4">Deadline</dt>
                        <dd class="col-sm-8">
                            {{ $tugas->deadline->format('d/m/Y H:i') }}
                            @if($tugas->deadline->isPast())
                                <span class="text-danger">(Sudah lewat {{ $tugas->deadline->diffForHumans(null, null, true) }})</span>
                            @else
                                <span class="text-success">({{ $tugas->deadline->diffForHumans(null, null, true) }} lagi)</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Nilai Maksimal</dt>
                        <dd class="col-sm-8">{{ $tugas->nilai_maksimal }}</dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($tugas->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                        </dd>
                        @if($tugas->auto_grade)
                            <dt class="col-sm-4">Penilaian Otomatis</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-info">
                                    <i class="bi bi-robot"></i> AI Grading Aktif
                                </span>
                            </dd>
                        @endif
                    </dl>

                    @if($canWork)
                        <hr>
                        <div class="text-center">
                            <form id="mulaiKerjaForm" action="{{ route('mahasiswa.tugas.start', $tugas) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalMulaiKerja">
                                    <i class="bi bi-play-circle"></i> Mulai Mengerjakan Tugas
                                </button>
                            </form>
                        </div>
                        <!-- Modal Konfirmasi Bootstrap -->
                        <div class="modal fade" id="modalMulaiKerja" tabindex="-1" aria-labelledby="modalMulaiKerjaLabel" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="modalMulaiKerjaLabel">Konfirmasi Mulai Tugas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                Apakah Anda yakin ingin mulai mengerjakan tugas ini? Setelah dimulai, waktu akan berjalan dan Anda harus menyelesaikan dalam satu kali kesempatan.
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary" id="btnKonfirmasiMulaiKerja">Ya, Mulai</button>
                              </div>
                            </div>
                          </div>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var btnKonfirmasi = document.getElementById('btnKonfirmasiMulaiKerja');
                            if(btnKonfirmasi) {
                                btnKonfirmasi.addEventListener('click', function() {
                                    document.getElementById('mulaiKerjaForm').submit();
                                });
                            }
                        });
                        </script>
                    @elseif($canContinue)
                        <hr>
                        <div class="text-center">
                            <a href="{{ route('mahasiswa.ujian.work', $jawaban) }}" class="btn btn-warning btn-lg">
                                <i class="bi bi-pencil"></i> Lanjutkan Mengerjakan
                            </a>
                        </div>
                    @elseif($jawaban)
                        <hr>
                        <div class="alert alert-info">
                            <h5 class="alert-heading">Status Pengumpulan</h5>
                            <p class="mb-0">
                                @if($jawaban->status === 'draft')
                                    <strong>Status:</strong> <span class="badge bg-warning">Draft</span> - Jawaban belum disubmit
                                @elseif($jawaban->status === 'submitted')
                                    <strong>Status:</strong> <span class="badge bg-info">Submitted</span> - Jawaban sudah disubmit, menunggu penilaian
                                @elseif($jawaban->status === 'graded')
                                    <strong>Status:</strong> <span class="badge bg-success">Graded</span> - Sudah dinilai
                                    <br><strong>Nilai Akhir:</strong> {{ $jawaban->nilai_akhir }}
                                @endif
                            </p>
                        </div>

                        {{-- AI Grading Results Section --}}
                        @if($tugas->auto_grade && $jawaban->status !== 'draft')
                            @php
                                $penilaian = $jawaban->penilaian;
                                $hasAIGrading = $penilaian && $penilaian->status_penilaian === 'ai_graded';
                                $aiScore = $hasAIGrading ? $jawaban->nilai_akhir : null;
                            @endphp
                            
                            @if($hasAIGrading && $aiScore !== null)
                                <div class="alert alert-success">
                                    <h5 class="alert-heading">
                                        <i class="bi bi-robot"></i> Hasil Penilaian AI
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Nilai AI:</strong> 
                                            <span class="badge bg-success fs-6">{{ $aiScore }}</span>
                                            <br>
                                            <small class="text-muted">Dinilai otomatis dengan AI Gemini</small>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Status:</strong> 
                                            <span class="badge bg-info">{{ ucfirst($penilaian->status_penilaian) }}</span>
                                            <br>
                                            <small class="text-muted">Waktu: {{ $penilaian->graded_at ? $penilaian->graded_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                        </div>
                                    </div>
                                    
                                    @if($penilaian->feedback_ai)
                                        <hr>
                                        <div class="mt-3">
                                            <strong>Feedback AI:</strong>
                                            <div class="mt-2 p-3 bg-light rounded">
                                                {!! nl2br(e($penilaian->feedback_ai)) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @elseif($jawaban->status === 'submitted')
                                <div class="alert alert-warning">
                                    <h5 class="alert-heading">
                                        <i class="bi bi-clock"></i> Penilaian AI Sedang Diproses
                                    </h5>
                                    <p class="mb-0">
                                        Jawaban Anda sedang dinilai otomatis dengan AI. 
                                        Hasil penilaian akan muncul dalam beberapa saat.
                                    </p>
                                </div>
                            @endif
                        @endif
                    @elseif($isExpired)
                        <div class="alert alert-danger mt-4">Tugas sudah expired. Anda tidak dapat mengumpulkan jawaban.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 