@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Nilai</h4>
                    <a href="{{ route('mahasiswa.nilai.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Mata Kuliah</dt>
                        <dd class="col-sm-8">{{ $jawaban->tugas->mataKuliah->nama_mk }}</dd>
                        <dt class="col-sm-4">Judul</dt>
                        <dd class="col-sm-8">{{ $jawaban->tugas->judul }}</dd>
                        <dt class="col-sm-4">Jawaban Anda</dt>
                        <dd class="col-sm-8">{!! nl2br(e($jawaban->isi_jawaban)) !!}</dd>
                        <dt class="col-sm-4">Nilai</dt>
                        <dd class="col-sm-8">
                            @if($jawaban->penilaian)
                                <span class="badge bg-success">{{ $jawaban->penilaian->nilai_final }}</span>
                            @else
                                <span class="badge bg-secondary">Belum dinilai</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Feedback</dt>
                        <dd class="col-sm-8">
                            @if($jawaban->penilaian && $jawaban->penilaian->feedback)
                                {{ $jawaban->penilaian->feedback }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 