@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Dashboard Dosen</h4>
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
                                            <h5>{{ $totalTugas }}</h5>
                                            <p class="mb-0">Total Tugas</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-tasks fa-2x"></i>
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
                                            <h5>{{ $tugasAktif }}</h5>
                                            <p class="mb-0">Tugas Aktif</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
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
                                            <h5>{{ $jawabanMenunggu }}</h5>
                                            <p class="mb-0">Menunggu Penilaian</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clipboard-check fa-2x"></i>
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
                                    <h5>Aksi Cepat</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <a href="{{ route('dosen.tugas.create') }}" class="btn btn-primary btn-block">
                                                <i class="fas fa-plus"></i> Buat Tugas
                                            </a>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="{{ route('dosen.tugas.index') }}" class="btn btn-success btn-block">
                                                <i class="fas fa-list"></i> Kelola Tugas
                                            </a>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="{{ route('dosen.mata-kuliah.index') }}" class="btn btn-info btn-block">
                                                <i class="fas fa-book"></i> Mata Kuliah
                                            </a>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="{{ route('dosen.kelas.index') }}" class="btn btn-warning btn-block">
                                                <i class="fas fa-chalkboard"></i> Kelas
                                            </a>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="{{ route('dosen.penilaian.index') }}" class="btn btn-secondary btn-block">
                                                <i class="fas fa-star"></i> Penilaian
                                            </a>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="{{ route('voyager.dashboard') }}" class="btn btn-dark btn-block">
                                                <i class="fas fa-cog"></i> Admin Panel
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
                                                    <p class="mb-1">{{ $tugas->kelas && $tugas->kelas->mataKuliah ? $tugas->kelas->mataKuliah->nama_mk : '-' }}</p>
                                                    <small>Deadline: {{ $tugas->deadline->format('d/m/Y H:i') }}</small>
                                                    <div class="mt-2">
                                                        <a href="{{ route('dosen.tugas.show', $tugas) }}" class="btn btn-sm btn-outline-primary">
                                                            Lihat Detail
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">Belum ada tugas yang dibuat.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Jawaban Terbaru -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Jawaban Terbaru</h5>
                                </div>
                                <div class="card-body">
                                    @if($jawabanTerbaru->count() > 0)
                                        <div class="list-group">
                                            @foreach($jawabanTerbaru as $jawaban)
                                                <div class="list-group-item">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1">{{ $jawaban->mahasiswa->name }}</h6>
                                                        <small>{{ $jawaban->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="mb-1">{{ $jawaban->tugas->judul }}</p>
                                                    <small>Status: 
                                                        <span class="badge badge-warning">{{ ucfirst($jawaban->status) }}</span>
                                                    </small>
                                                    <div class="mt-2">
                                                        <a href="{{ route('dosen.penilaian.jawaban', $jawaban) }}" class="btn btn-sm btn-outline-primary">
                                                            Lihat Jawaban
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">Belum ada jawaban yang masuk.</p>
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

