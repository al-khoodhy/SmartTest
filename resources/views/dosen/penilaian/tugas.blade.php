@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Penilaian Tugas</h4>
                    <a href="{{ route('dosen.tugas.show', $tugas) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Detail Tugas
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Judul Tugas</dt>
                        <dd class="col-sm-8">{{ $tugas->judul }}</dd>
                        <dt class="col-sm-4">Mata Kuliah</dt>
                        <dd class="col-sm-8">{{ $tugas->mataKuliah->nama_mk }}</dd>
                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $tugas->deskripsi }}</dd>
                        <dt class="col-sm-4">Deadline</dt>
                        <dd class="col-sm-8">{{ $tugas->deadline->format('d/m/Y H:i') }} ({{ $tugas->deadline->diffForHumans() }})</dd>
                    </dl>
                    <h5 class="mt-4">Daftar Jawaban Mahasiswa</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>Status</th>
                                    <th>Nilai AI</th>
                                    <th>Nilai Manual</th>
                                    <th>Nilai Final</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jawaban as $i => $j)
                                    <tr>
                                        <td>{{ $jawaban->firstItem() + $i }}</td>
                                        <td>{{ $j->mahasiswa->name ?? '-' }}</td>
                                        <td>
                                            @if($j->status == 'graded')
                                                <span class="badge bg-success">Graded</span>
                                            @elseif($j->status == 'submitted')
                                                <span class="badge bg-info">Submitted</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($j->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $j->penilaian->nilai_ai ?? '-' }}</td>
                                        <td>{{ $j->penilaian->nilai_manual ?? '-' }}</td>
                                        <td>{{ $j->penilaian->nilai_final ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('dosen.penilaian.jawaban', $j) }}" class="btn btn-info btn-sm">Lihat Jawaban</a>
                                            <a href="{{ route('dosen.penilaian.grade', $j) }}" class="btn btn-success btn-sm">Nilai</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada jawaban mahasiswa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div>
                            {{ $jawaban->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 