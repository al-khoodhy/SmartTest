@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Tugas</h4>
                    <div>
                        <a href="{{ route('dosen.tugas.edit', $tugas) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('dosen.tugas.toggle-status', $tugas) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $tugas->is_active ? 'secondary' : 'success' }} me-2" 
                                    onclick="return confirm('Yakin ingin mengubah status tugas?')">
                                <i class="fas fa-{{ $tugas->is_active ? 'pause' : 'play' }}"></i> 
                                {{ $tugas->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        @if($tugas->jawabanMahasiswa->count() == 0)
                            <form action="{{ route('dosen.tugas.destroy', $tugas) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger me-2" 
                                        onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('dosen.tugas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Judul Tugas</dt>
                        <dd class="col-sm-8">{{ $tugas->judul }}</dd>

                        <dt class="col-sm-4">Mata Kuliah</dt>
                        <dd class="col-sm-8">{{ $tugas->mataKuliah->nama_mk }}</dd>

                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $tugas->deskripsi }}</dd>

                        <dt class="col-sm-4">Daftar Soal</dt>
                        <dd class="col-sm-8">
                            <ol>
                                @foreach($tugas->soal as $soal)
                                    <li>
                                        <div><strong>Pertanyaan:</strong> {{ $soal->pertanyaan }}</div>
                                        <div><strong>Bobot:</strong> {{ $soal->bobot }}</div>
                                    </li>
                                @endforeach
                            </ol>
                        </dd>

                        <dt class="col-sm-4">Rubrik Penilaian</dt>
                        <dd class="col-sm-8">{!! nl2br(e($tugas->rubrik_penilaian)) !!}</dd>

                        <dt class="col-sm-4">Deadline</dt>
                        <dd class="col-sm-8">
                            {{ $tugas->deadline->format('d/m/Y H:i') }}
                            @if($tugas->deadline->isPast())
                                <span class="text-danger">(Sudah lewat {{ $tugas->deadline->diffForHumans(null, null, true) }})</span>
                            @else
                                <span class="text-success">({{ $tugas->deadline->diffForHumans(null, null, true) }} lagi)</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Durasi (Menit)</dt>
                        <dd class="col-sm-8">{{ $tugas->durasi_menit }}</dd>

                        <dt class="col-sm-4">Nilai Maksimal</dt>
                        <dd class="col-sm-8">{{ $tugas->nilai_maksimal }}</dd>

                        <dt class="col-sm-4">Penilaian Otomatis</dt>
                        <dd class="col-sm-8">
                            @if($tugas->auto_grade)
                                <span class="badge bg-info">Ya</span>
                            @else
                                <span class="badge bg-warning text-dark">Tidak</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($tugas->status == 'active')
                                <span class="badge bg-success">Aktif</span>
                            @elseif($tugas->status == 'expired')
                                <span class="badge bg-danger">Expired</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                        </dd>
                    </dl>
                    
                    <!-- Statistik -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Statistik Tugas</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $totalMahasiswa }}</h4>
                                            <small>Total Mahasiswa</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $sudahMengerjakan }}</h4>
                                            <small>Sudah Mengerjakan</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $sudahSubmit }}</h4>
                                            <small>Sudah Submit</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $sudahDinilai }}</h4>
                                            <small>Sudah Dinilai</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tombol untuk melihat jawaban -->
                    @if($sudahMengerjakan > 0)
                        <div class="mt-4">
                            <a href="{{ route('dosen.penilaian.tugas', $tugas) }}" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Lihat Jawaban Mahasiswa
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 