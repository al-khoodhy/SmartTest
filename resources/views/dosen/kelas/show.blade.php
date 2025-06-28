@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Kelas: {{ $kelas->nama_kelas }}</h4>
                    <div>
                        <a href="{{ route('dosen.kelas.edit', $kelas) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('dosen.kelas.index') }}" class="btn btn-secondary">
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
                            <h5>Informasi Kelas</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Nama Kelas:</strong></td>
                                    <td>{{ $kelas->nama_kelas }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Mata Kuliah:</strong></td>
                                    <td>{{ $kelas->mataKuliah->kode_mk }} - {{ $kelas->mataKuliah->nama_mk }}</td>
                                </tr>
                                <tr>
                                    <td><strong>SKS:</strong></td>
                                    <td>{{ $kelas->mataKuliah->sks }} SKS</td>
                                </tr>
                                <tr>
                                    <td><strong>Dosen Pengampu:</strong></td>
                                    <td>
                                        @foreach($kelas->dosen as $d)
                                            <span class="badge badge-primary">{{ $d->name }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Statistik</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $enrollments->count() }}</h3>
                                            <p class="mb-0">Mahasiswa</p>
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
                            <h5>Daftar Mahasiswa</h5>
                            @if($enrollments->count() > 0)
                                <div class="list-group">
                                    @foreach($enrollments as $enrollment)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $enrollment->mahasiswa->name }}</h6>
                                                <small class="text-muted">{{ $enrollment->mahasiswa->nim_nip }}</small>
                                            </div>
                                            <span class="badge badge-{{ $enrollment->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-user-graduate fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada mahasiswa yang terdaftar di kelas ini.</p>
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
                                    <p class="text-muted">Belum ada tugas untuk kelas ini.</p>
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