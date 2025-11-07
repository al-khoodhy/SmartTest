@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Profil Dosen</h4>
                    <div>
                        <a href="{{ route('dosen.profile.edit') }}" class="btn btn-warning me-2">
                            <i class="bi bi-pencil"></i> Edit Profil
                        </a>
                        <a href="{{ route('dosen.profile.change-password') }}" class="btn btn-info">
                            <i class="bi bi-key"></i> Ubah Password
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Informasi Pribadi -->
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="bi bi-person-circle" style="font-size: 6rem; color: #007bff;"></i>
                            </div>
                            <h5>{{ $dosen->name }}</h5>
                            <p class="text-muted">{{ $dosen->role ? $dosen->role->name : 'Dosen' }}</p>
                        </div>
                        <div class="col-md-8">
                            <h5>Informasi Pribadi</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Nama Lengkap:</strong></td>
                                    <td>{{ $dosen->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $dosen->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>NIP/NIDN:</strong></td>
                                    <td>{{ $dosen->nim_nip ?? '-' }}</td>
                                </tr>
                                <!-- <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($dosen->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>
                                </tr> -->
                                <tr>
                                    <td><strong>Bergabung Sejak:</strong></td>
                                    <td>{{ $dosen->created_at ? $dosen->created_at->format('d F Y') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Statistik Mengajar -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Statistik Mengajar</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $dosen->kelasAsDosen->count() }}</h4>
                                            <small>Total Kelas</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $dosen->mataKuliahDiampu->count() }}</h4>
                                            <small>Mata Kuliah</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $dosen->tugasDibuat->count() }}</h4>
                                            <small>Total Tugas</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $dosen->penilaianDilakukan->count() }}</h4>
                                            <small>Penilaian</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mata Kuliah yang Diampu -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Mata Kuliah yang Diampu</h5>
                            @if($dosen->mataKuliahDiampu->count() > 0)
                                <div class="list-group">
                                    @foreach($dosen->mataKuliahDiampu as $mataKuliah)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">{{ $mataKuliah->nama_mk }}</h6>
                                                    <small class="text-muted">{{ $mataKuliah->kode_mk }} - {{ $mataKuliah->sks }} SKS</small>
                                                </div>
                                                <span class="badge bg-primary">{{ $mataKuliah->kelas->count() }} Kelas</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-book fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada mata kuliah yang diampu.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Kelas yang Diampu -->
                        <div class="col-md-6">
                            <h5>Kelas yang Diampu</h5>
                            @if($dosen->kelasAsDosen->count() > 0)
                                <div class="list-group">
                                    @foreach($dosen->kelasAsDosen as $kelas)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">{{ $kelas->nama_kelas }}</h6>
                                                    <small class="text-muted">{{ $kelas->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</small>
                                                </div>
                                                <a href="{{ route('dosen.kelas.show', $kelas) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-people fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada kelas yang diampu.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tugas Terbaru -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Tugas Terbaru</h5>
                            @if($dosen->tugasDibuat->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Judul Tugas</th>
                                                <th>Mata Kuliah</th>
                                                <th>Status</th>
                                                <th>Tanggal Dibuat</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dosen->tugasDibuat->take(5) as $tugas)
                                                <tr>
                                                    <td>{{ $tugas->judul }}</td>
                                                    <td>{{ $tugas->kelas->mataKuliah->nama_mk ?? '-' }}</td>
                                                    <td>
                                                        @if($tugas->is_active)
                                                            <span class="badge bg-success">Aktif</span>
                                                        @else
                                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $tugas->created_at->format('d/m/Y') }}</td>
                                                    <td>
                                                        <a href="{{ route('dosen.tugas.show', $tugas) }}" class="btn btn-sm btn-info">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-journal-text fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">Belum ada tugas yang dibuat.</p>
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