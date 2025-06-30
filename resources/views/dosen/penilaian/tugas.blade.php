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
                    <div class="mb-2">
                        <span class="badge bg-primary text-light" style="font-size:1em;">Jumlah Jawaban: {{ $jawaban->total() }}</span>
                    </div>
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
                                                <span class="badge bg-success">Sudah Dinilai</span>
                                            @elseif($j->status == 'submitted')
                                                <span class="badge bg-warning text-dark">Menunggu Penilaian</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($j->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tugas->auto_grade && $j->nilai_ai > 0)
                                                <span class="badge bg-info text-dark">{{ $j->nilai_ai }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($j->status == 'graded' && $j->nilai_manual > 0)
                                                <span class="badge bg-success text-light">{{ $j->nilai_manual }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($j->status == 'graded' && $j->nilai_akhir > 0)
                                                <span class="badge bg-primary text-light fs-6">{{ $j->nilai_akhir }}</span>
                                            @elseif($tugas->auto_grade && $j->nilai_ai > 0)
                                                <span class="badge bg-info text-dark">{{ $j->nilai_ai }}</span>
                                                <br><small class="text-muted">Nilai AI</small>
                                            @else
                                                <span class="badge bg-warning text-dark">Menunggu Penilaian</span>
                                            @endif
                                        </td>
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