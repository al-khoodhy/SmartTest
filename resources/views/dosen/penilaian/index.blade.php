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
                    <!-- Filter Form -->
                    <form class="mb-4" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="kelas_id">Mata Kuliah/Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-control">
                                    <option value="">Semua</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->mataKuliah->nama_mk ?? '-' }} - {{ $k->nama_kelas }}
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
                                <a href="{{ route('dosen.penilaian.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                    @if($tugas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Tugas</th>
                                        <th>Mata Kuliah</th>
                                        <th>Status Penilaian</th>
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
                                                @php
                                                    $totalJawaban = $t->jawabanMahasiswa->count();
                                                    $gradedJawaban = $t->jawabanMahasiswa->where('status', 'graded')->count();
                                                    $aiGradedJawaban = $t->jawabanMahasiswa->filter(function($jawaban) {
                                                        return $jawaban->penilaian && $jawaban->penilaian->status_penilaian === 'ai_graded';
                                                    })->count();
                                                @endphp
                                                
                                                @if($totalJawaban == 0)
                                                    <span class="badge bg-secondary text-light">Belum Ada Jawaban</span>
                                                @elseif($gradedJawaban == $totalJawaban)
                                                    <span class="badge bg-success text-light">Semua Sudah Dinilai</span>
                                                @elseif($t->auto_grade && $aiGradedJawaban > 0)
                                                    <span class="badge bg-info text-dark">{{ $aiGradedJawaban }}/{{ $totalJawaban }} AI Graded</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">{{ $gradedJawaban }}/{{ $totalJawaban }} Dinilai</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary text-light" style="font-size:1em; min-width:2.5em;">{{ $totalJawaban }}</span>
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