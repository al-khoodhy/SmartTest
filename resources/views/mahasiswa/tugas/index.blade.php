@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Kumpulan Tugas</h4>
                    <p class="mb-0">Daftar tugas dari semua mata kuliah yang Anda ambil</p>
                </div>

                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="mata_kuliah_id" class="form-control">
                                    <option value="">Semua Mata Kuliah</option>
                                    @foreach($mataKuliah as $mk)
                                        <option value="{{ $mk->id }}" {{ request('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                                            {{ $mk->nama_mk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Sudah Dikerjakan</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('mahasiswa.tugas.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Tugas List -->
                    @if($tugas->count() > 0)
                        <div class="row">
                            @foreach($tugas as $t)
                                @php
                                    $jawaban = $jawabanStatus[$t->id] ?? null;
                                    $now = \Carbon\Carbon::now();
                                    $isExpired = $t->deadline <= $now;
                                    $canWork = !$jawaban && !$isExpired && $t->is_active;
                                    $canContinue = $jawaban && $jawaban->status === 'draft' && !$isExpired;
                                @endphp
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">{{ $t->mataKuliah->nama_mk }}</h6>
                                            @if($jawaban)
                                                @if($jawaban->status === 'draft')
                                                    <span class="badge badge-warning">Sedang Dikerjakan</span>
                                                @elseif($jawaban->status === 'submitted')
                                                    <span class="badge badge-info">Menunggu Penilaian</span>
                                                @elseif($jawaban->status === 'graded')
                                                    <span class="badge badge-success">Sudah Dinilai</span>
                                                @endif
                                            @elseif($isExpired)
                                                <span class="badge badge-danger">Expired</span>
                                            @else
                                                <span class="badge badge-primary">Tersedia</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $t->judul }}</h5>
                                            <p class="card-text">{{ Str::limit($t->deskripsi, 100) }}</p>
                                            
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> {{ $t->dosen->name }}<br>
                                                    <i class="fas fa-clock"></i> {{ $t->durasi_menit }} menit<br>
                                                    <i class="fas fa-calendar"></i> Deadline: {{ $t->deadline->format('d/m/Y H:i') }}<br>
                                                    <i class="fas fa-star"></i> Nilai Maksimal: {{ $t->nilai_maksimal }}
                                                    @if($t->auto_grade)
                                                        <br><i class="fas fa-robot"></i> Auto Grade
                                                    @endif
                                                </small>
                                            </div>

                                            @if($jawaban && $jawaban->penilaian)
                                                <div class="alert alert-success">
                                                    <strong>Nilai: {{ $jawaban->penilaian->nilai_final }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('mahasiswa.tugas.show', $t) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Lihat Detail
                                                </a>
                                                
                                                @if($canWork)
                                                    <form action="{{ route('mahasiswa.tugas.start', $t) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" 
                                                                onclick="return confirm('Mulai mengerjakan tugas ini?')">
                                                            <i class="fas fa-play"></i> Mulai Kerjakan
                                                        </button>
                                                    </form>
                                                @elseif($canContinue)
                                                    <a href="{{ route('mahasiswa.ujian.work', $jawaban) }}" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Lanjutkan
                                                    </a>
                                                @elseif($jawaban && in_array($jawaban->status, ['submitted', 'graded']))
                                                    <a href="{{ route('mahasiswa.nilai.show', $jawaban) }}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-chart-line"></i> Lihat Nilai
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $tugas->appends(request()->query())->links('vendor.pagination.simple-numeric') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5>Tidak ada tugas</h5>
                            <p class="text-muted">
                                @if(request()->has('mata_kuliah_id') || request()->has('status'))
                                    Tidak ada tugas yang sesuai dengan filter yang dipilih.
                                @else
                                    Belum ada tugas yang tersedia untuk Anda.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

