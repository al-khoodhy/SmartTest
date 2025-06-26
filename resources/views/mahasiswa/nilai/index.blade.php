@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Nilai</h4>
                </div>
                <div class="card-body">
                    @if($jawaban->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>Judul</th>
                                        <th>Nilai</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jawaban as $n)
                                        <tr>
                                            <td>{{ $n->tugas->mataKuliah->nama_mk }}</td>
                                            <td>{{ $n->tugas->judul }}</td>
                                            <td>
                                                @if($n->penilaian)
                                                    <span class="badge bg-success">{{ $n->penilaian->nilai_final }}</span>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($n->penilaian)
                                                    <span class="badge bg-success">Sudah Dinilai</span>
                                                @elseif($n->status === 'submitted')
                                                    <span class="badge bg-info">Menunggu Penilaian</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Belum Dikerjakan</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($n->penilaian)
                                                    <a href="{{ route('mahasiswa.nilai.show', $n) }}" class="btn btn-sm btn-info" title="Detail Nilai">
                                                        <i class="bi bi-bar-chart"></i>
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $jawaban->links('vendor.pagination.simple-numeric') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x fa-3x text-muted mb-3"></i>
                            <h5>Belum ada nilai</h5>
                            <p class="text-muted">Nilai tugas/ujian Anda akan muncul di sini setelah dinilai dosen.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 