@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 768px) {
        .table-responsive table {
            font-size: 0.8em;
        }
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Daftar Nilai</h4>
                        <div>
                            <a href="{{ route('mahasiswa.nilai.per-mata-kuliah') }}" class="btn btn-outline-primary me-2">
                                <i class="bi bi-bar-chart"></i> Nilai Per Mata Kuliah
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form class="mb-4" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="kelas_id">Mata Kuliah/Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-control">
                                    <option value="">Semua</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->kelas_id }}" {{ request('kelas_id') == $k->kelas_id ? 'selected' : '' }}>
                                            {{ $k->kelas->mataKuliah->nama_mk ?? '-' }} - {{ $k->kelas->nama_kelas ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status_penilaian">Status Penilaian</label>
                                <select name="status_penilaian" id="status_penilaian" class="form-control">
                                    <option value="">Semua</option>
                                    <option value="graded" {{ request('status_penilaian') == 'graded' ? 'selected' : '' }}>Sudah Dinilai</option>
                                    <option value="pending" {{ request('status_penilaian') == 'pending' ? 'selected' : '' }}>Menunggu Penilaian</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('mahasiswa.nilai.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <!-- Statistik -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $totalTugas }}</h5>
                                    <small>Total Tugas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $sudahDinilai }}</h5>
                                    <small>Sudah Dinilai</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $menungguPenilaian }}</h5>
                                    <small>Menunggu Penilaian</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>{{ number_format($rataRataNilai, 1) }}</h5>
                                    <small>Rata-rata Nilai</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($jawaban->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>Judul Tugas</th>
                                        <th>Nilai</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jawaban as $n)
                                        <tr>
                                            <td>
                                                <strong>{{ $n->tugas->mataKuliah->nama_mk }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $n->tugas->kelas->nama_kelas ?? '-' }}</small>
                                            </td>
                                            <td>{{ $n->tugas->judul }}</td>
                                            <td>
                                                @if($n->status === 'graded' && $n->nilai_akhir > 0)
                                                    <span class="badge bg-success fs-6">{{ $n->nilai_akhir }}</span>
                                                    @if($n->penilaian)
                                                        @if($n->penilaian->status_penilaian == 'ai_graded')
                                                            <br><small class="text-muted"><i class="bi bi-robot"></i> AI</small>
                                                        @elseif($n->penilaian->status_penilaian == 'final')
                                                            <br><small class="text-muted"><i class="bi bi-person-check"></i> Manual</small>
                                                        @endif
                                                    @endif
                                                @elseif($n->tugas->auto_grade && $n->nilai_ai > 0)
                                                    <span class="badge bg-info text-dark fs-6">{{ $n->nilai_ai }}</span>
                                                    <br><small class="text-muted"><i class="bi bi-robot"></i> AI</small>
                                                @elseif($n->status === 'submitted')
                                                    <span class="badge bg-info">Menunggu Penilaian</span>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($n->status === 'graded')
                                                    <span class="badge bg-success">Sudah Dinilai</span>
                                                @elseif($n->tugas->auto_grade && $n->nilai_ai > 0)
                                                    <span class="badge bg-info text-dark">AI Graded</span>
                                                @elseif($n->status === 'submitted')
                                                    <span class="badge bg-warning text-dark">Menunggu Penilaian</span>
                                                @else
                                                    <span class="badge bg-secondary">Belum Dikerjakan</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $n->waktu_selesai ? $n->waktu_selesai->format('d/m/Y H:i') : '-' }}</small>
                                            </td>
                                            <td>
                                                @if($n->penilaian || ($n->tugas->auto_grade && $n->nilai_ai > 0))
                                                    <a href="{{ route('mahasiswa.nilai.show', $n) }}" class="btn btn-sm btn-info" title="Detail Nilai & Feedback">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
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