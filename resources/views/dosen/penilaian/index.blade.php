@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Kelola Penilaian Tugas</h4>
                </div>
                <div class="card-body">
                    @if($tugas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Tugas</th>
                                        <th>Mata Kuliah</th>
                                        <th>Jumlah Jawaban</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tugas as $i => $t)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>
                                                <strong>{{ $t->judul }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Str::limit($t->deskripsi, 50) }}</small>
                                            </td>
                                            <td>{{ $t->mataKuliah->nama_mk ?? '-' }}</td>
                                            <td>
                                                <span class="badge badge-primary">{{ $t->jawabanMahasiswa->count() }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('dosen.penilaian.tugas', ['tugas' => $t->id]) }}" class="btn btn-sm btn-info" title="Detail Penilaian">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $tugas->appends(request()->query())->links('vendor.pagination.simple-numeric') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5>Belum ada tugas untuk dinilai</h5>
                            <p class="text-muted">Tugas yang sudah dikumpulkan mahasiswa akan muncul di sini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 