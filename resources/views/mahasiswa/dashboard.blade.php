@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Dashboard Mahasiswa</h4>
                    <p class="mb-0">Selamat datang, {{ auth()->user()->name }}</p>
                </div>

                <div class="card-body">
                    <!-- Statistik Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>{{ $totalMataKuliah }}</h5>
                                            <p class="mb-0">Mata Kuliah</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-book fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>{{ $tugasSelesai }}</h5>
                                            <p class="mb-0">Tugas Selesai</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>{{ $tugasTersedia }}</h5>
                                            <p class="mb-0">Tugas Tersedia</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-tasks fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>{{ number_format($rataRataNilai, 1) }}</h5>
                                            <p class="mb-0">Rata-rata Nilai</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-star fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Menu Utama</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <a href="{{ route('mahasiswa.tugas.index') }}" class="btn btn-primary btn-block btn-lg">
                                                <i class="fas fa-tasks"></i><br>
                                                <span>Kumpulan Tugas</span>
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="{{ route('mahasiswa.nilai.index') }}" class="btn btn-success btn-block btn-lg">
                                                <i class="fas fa-chart-line"></i><br>
                                                <span>Kumpulan Nilai</span>
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="{{ route('mahasiswa.nilai.per-mata-kuliah') }}" class="btn btn-info btn-block btn-lg">
                                                <i class="fas fa-graduation-cap"></i><br>
                                                <span>Nilai per Mata Kuliah</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Tugas Terbaru -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Tugas Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    @if($tugasTerbaru->count() > 0)
                                        <div class="list-group">
                                            @foreach($tugasTerbaru as $tugas)
                                                <div class="list-group-item">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1">{{ $tugas->judul }}</h6>
                                                        <small>{{ $tugas->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="mb-1">{{ $tugas->mataKuliah->nama_mk }}</p>
                                                    <small>Deadline: {{ $tugas->deadline->format('d/m/Y H:i') }}</small>
                                                    <div class="mt-2">
                                                        @php
                                                            $jawaban = auth()->user()->jawabanMahasiswa()->where('tugas_id', $tugas->id)->first();
                                                        @endphp
                                                        @if(!$jawaban)
                                                            <span class="badge badge-primary">Belum Dikerjakan</span>
                                                        @elseif($jawaban->status === 'draft')
                                                            <span class="badge badge-warning">Sedang Dikerjakan</span>
                                                        @else
                                                            <span class="badge badge-success">Sudah Dikerjakan</span>
                                                        @endif
                                                        <a href="{{ route('mahasiswa.tugas.show', $tugas) }}" class="btn btn-sm btn-outline-primary ml-2">
                                                            Lihat Detail
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">Belum ada tugas tersedia.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Nilai Terbaru -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Nilai Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    @if($nilaiTerbaru->count() > 0)
                                        <div class="list-group">
                                            @foreach($nilaiTerbaru as $jawaban)
                                                <div class="list-group-item">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1">{{ $jawaban->tugas->judul }}</h6>
                                                        <small>{{ $jawaban->penilaian->graded_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="mb-1">{{ $jawaban->tugas->mataKuliah->nama_mk }}</p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small>Nilai: 
                                                            <span class="badge badge-{{ $jawaban->nilai_akhir >= 75 ? 'success' : ($jawaban->nilai_akhir >= 60 ? 'warning' : 'danger') }}">
                                                                {{ $jawaban->nilai_akhir }}
                                                            </span>
                                                        </small>
                                                        <a href="{{ route('mahasiswa.nilai.show', $jawaban) }}" class="btn btn-sm btn-outline-primary">
                                                            Lihat Detail
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">Belum ada nilai yang tersedia.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

