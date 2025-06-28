@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Mata Kuliah yang Diampu</h4>
                    <a href="{{ route('dosen.mata-kuliah.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Mata Kuliah
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

                    @if($mataKuliah->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Status</th>
                                        <th>Jumlah Kelas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mataKuliah as $mk)
                                        <tr>
                                            <td>{{ $mk->kode_mk }}</td>
                                            <td>{{ $mk->nama_mk }}</td>
                                            <td>{{ $mk->sks }}</td>
                                            <td>
                                                @if($mk->is_active)
                                                    <span class="badge badge-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-secondary">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td>{{ $mk->kelas->count() }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('dosen.mata-kuliah.show', $mk) }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                    <a href="{{ route('dosen.mata-kuliah.edit', $mk) }}" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('dosen.mata-kuliah.destroy', $mk) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?')"
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
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada mata kuliah</h5>
                            <p class="text-muted">Mulai dengan menambahkan mata kuliah yang Anda ampu.</p>
                            <a href="{{ route('dosen.mata-kuliah.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Mata Kuliah Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 