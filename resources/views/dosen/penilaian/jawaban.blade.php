@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Jawaban Mahasiswa</h4>
                    <a href="{{ route('dosen.penilaian.tugas', $jawaban->tugas) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Jawaban
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nama Mahasiswa</dt>
                        <dd class="col-sm-8">{{ $jawaban->mahasiswa->name ?? '-' }}</dd>
                        <dt class="col-sm-4">Judul Tugas</dt>
                        <dd class="col-sm-8">{{ $jawaban->tugas->judul ?? '-' }}</dd>
                        <dt class="col-sm-4">Mata Kuliah</dt>
                        <dd class="col-sm-8">{{ $jawaban->tugas->mataKuliah->nama_mk ?? '-' }}</dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($jawaban->status == 'graded')
                                <span class="badge bg-success">Graded</span>
                            @elseif($jawaban->status == 'submitted')
                                <span class="badge bg-info">Submitted</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($jawaban->status) }}</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Jawaban Mahasiswa</dt>
                        <dd class="col-sm-8"><div class="border p-2 bg-light">{!! nl2br(e($jawaban->jawaban)) !!}</div></dd>
                        @if($jawaban->penilaian)
                            <dt class="col-sm-4">Nilai AI</dt>
                            <dd class="col-sm-8">{{ $jawaban->penilaian->nilai_ai ?? '-' }}</dd>
                            <dt class="col-sm-4">Nilai Manual</dt>
                            <dd class="col-sm-8">{{ $jawaban->penilaian->nilai_manual ?? '-' }}</dd>
                            <dt class="col-sm-4">Nilai Final</dt>
                            <dd class="col-sm-8">{{ $jawaban->penilaian->nilai_final ?? '-' }}</dd>
                            <dt class="col-sm-4">Feedback AI</dt>
                            <dd class="col-sm-8">{{ $jawaban->penilaian->feedback_ai ?? '-' }}</dd>
                            <dt class="col-sm-4">Feedback Manual</dt>
                            <dd class="col-sm-8">{{ $jawaban->penilaian->feedback_manual ?? '-' }}</dd>
                        @endif
                        @if($jawaban->status === 'graded')
                            <div class="alert alert-success">
                                <strong>Nilai Akhir (total): {{ $jawaban->nilai_akhir }}</strong>
                            </div>
                        @endif
                    </dl>
                    <div class="mt-3">
                        <a href="{{ route('dosen.penilaian.grade', $jawaban) }}" class="btn btn-success">Nilai Jawaban</a>
                        <a href="{{ route('dosen.penilaian.tugas', $jawaban->tugas) }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 