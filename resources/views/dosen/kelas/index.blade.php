@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Kelas yang Diampu</h4>
                    <a href="{{ route('dosen.kelas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Kelas
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($kelas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nama Kelas</th>
                                        <th>Mata Kuliah</th>
                                        <th>Jumlah Mahasiswa</th>
                                        <th>Jumlah Tugas</th>
                                        <th>Dosen Pengampu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kelas as $k)
                                        <tr>
                                            <td>{{ $k->nama_kelas }}</td>
                                            <td>
                                                <strong>{{ $k->mataKuliah->kode_mk }}</strong><br>
                                                <small class="text-muted">{{ $k->mataKuliah->nama_mk }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $k->enrollments->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">{{ $k->tugas->count() }}</span>
                                            </td>
                                            <td>
                                                @foreach($k->dosen as $d)
                                                    <span class="badge badge-secondary">{{ $d->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('dosen.kelas.show', $k) }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                    <a href="{{ route('dosen.kelas.edit', $k) }}" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('dosen.kelas.destroy', $k) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Yakin ingin menghapus kelas ini?')"
                                                          style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada kelas</h5>
                            <p class="text-muted">Mulai dengan menambahkan kelas yang Anda ampu.</p>
                            <a href="{{ route('dosen.kelas.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Kelas Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 