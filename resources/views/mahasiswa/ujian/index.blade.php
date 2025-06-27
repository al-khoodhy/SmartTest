@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Ujian</h4>
                </div>
                <div class="card-body">
                    @if($ujian->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>Judul Ujian</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ujian as $u)
                                        <tr>
                                            <td>{{ $u->mataKuliah->nama_mk }}</td>
                                            <td>{{ $u->judul }}</td>
                                            <td>{{ $u->deadline->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($u->jawaban && $u->jawaban->status === 'submitted')
                                                    <span class="badge bg-info">Sudah Dikerjakan</span>
                                                @elseif($u->deadline < now())
                                                    <span class="badge bg-danger">Expired</span>
                                                @else
                                                    <span class="badge bg-primary">Tersedia</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$u->jawaban && $u->deadline >= now())
                                                    <form action="{{ route('mahasiswa.tugas.start', $u) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Mulai Ujian">
                                                            <i class="bi bi-play-circle"></i> Mulai
                                                        </button>
                                                    </form>
                                                @elseif($u->jawaban && $u->jawaban->status === 'draft')
                                                    <a href="{{ route('mahasiswa.ujian.work', $u->jawaban) }}" class="btn btn-sm btn-warning" title="Lanjutkan Ujian">
                                                        <i class="bi bi-pencil-square"></i> Lanjutkan
                                                    </a>
                                                @elseif($u->jawaban && $u->jawaban->status === 'submitted')
                                                    <a href="{{ route('mahasiswa.nilai.show', $u->jawaban) }}" class="btn btn-sm btn-info" title="Lihat Nilai">
                                                        <i class="bi bi-bar-chart"></i> Nilai
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
                            {{ $ujian->links('vendor.pagination.simple-numeric') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x fa-3x text-muted mb-3"></i>
                            <h5>Belum ada ujian</h5>
                            <p class="text-muted">Ujian yang tersedia akan muncul di sini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 