@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Mata Kuliah: {{ $mataKuliah->nama_mk }}</h4>
                    <div>
                        <a href="{{ route('dosen.mata-kuliah.edit', $mataKuliah) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('dosen.mata-kuliah.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informasi Mata Kuliah</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Kode:</strong></td>
                                    <td>{{ $mataKuliah->kode_mk }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td>{{ $mataKuliah->nama_mk }}</td>
                                </tr>
                                <tr>
                                    <td><strong>SKS:</strong></td>
                                    <td>{{ $mataKuliah->sks }} SKS</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($mataKuliah->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Deskripsi:</strong></td>
                                    <td>{{ $mataKuliah->deskripsi ?: 'Tidak ada deskripsi' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Statistik</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $kelas->count() }}</h3>
                                            <p class="mb-0">Kelas</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $tugas->count() }}</h3>
                                            <p class="mb-0">Tugas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Kelas yang Diampu</h5>
                            @if($kelas->count() > 0)
                                <div class="list-group">
                                    @foreach($kelas as $k)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $k->nama_kelas }}</h6>
                                                <small class="text-muted">
                                                    Dosen: 
                                                    @foreach($k->dosen as $d)
                                                        {{ $d->name }}{{ !$loop->last ? ', ' : '' }}
                                                    @endforeach
                                                </small>
                                            </div>
                                            <a href="{{ route('dosen.kelas.show', $k) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada kelas untuk mata kuliah ini.</p>
                                    <a href="{{ route('dosen.kelas.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah Kelas
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5>Tugas Terbaru</h5>
                            @if($tugas->count() > 0)
                                <div class="list-group">
                                    @foreach($tugas->take(5) as $t)
                                        <div class="list-group-item">
                                            <h6 class="mb-1">{{ $t->judul }}</h6>
                                            <p class="mb-1 text-muted">{{ Str::limit($t->deskripsi, 100) }}</p>
                                            <small class="text-muted">
                                                Deadline: {{ $t->deadline->format('d/m/Y H:i') }} | 
                                                Status: 
                                                @if($t->is_active)
                                                    <span class="badge badge-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-secondary">Nonaktif</span>
                                                @endif
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-tasks fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada tugas untuk mata kuliah ini.</p>
                                    <a href="{{ route('dosen.tugas.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Buat Tugas
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 