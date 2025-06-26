@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Form Penilaian Jawaban Mahasiswa</h4>
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
                        <dt class="col-sm-4">Jawaban Mahasiswa</dt>
                        <dd class="col-sm-8">
                            <ol>
                                @foreach($jawaban->jawabanSoal as $jawabanSoal)
                                    <li class="mb-3">
                                        <div><strong>Pertanyaan:</strong> {{ $jawabanSoal->soal->pertanyaan ?? '-' }}</div>
                                        <div><strong>Jawaban:</strong> {!! nl2br(e($jawabanSoal->jawaban)) !!}</div>
                                        <div class="mb-2">
                                            <label>Nilai Manual</label>
                                            <input type="number" name="nilai_manual[{{ $jawabanSoal->id }}]" class="form-control @error('nilai_manual.' . $jawabanSoal->id) is-invalid @enderror" min="0" max="{{ $jawaban->tugas->nilai_maksimal }}" value="{{ old('nilai_manual.' . $jawabanSoal->id, optional($jawabanSoal->penilaian)->nilai_manual) }}" required>
                                            @error('nilai_manual.' . $jawabanSoal->id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-2">
                                            <label>Feedback Manual</label>
                                            <textarea name="feedback_manual[{{ $jawabanSoal->id }}]" class="form-control @error('feedback_manual.' . $jawabanSoal->id) is-invalid @enderror" rows="2" required>{{ old('feedback_manual.' . $jawabanSoal->id, optional($jawabanSoal->penilaian)->feedback_manual) }}</textarea>
                                            @error('feedback_manual.' . $jawabanSoal->id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </dd>
                        @if($jawaban->penilaian)
                            <dt class="col-sm-4">Nilai AI</dt>
                            <dd class="col-sm-8">{{ $jawaban->penilaian->nilai_ai ?? '-' }}</dd>
                            <dt class="col-sm-4">Feedback AI</dt>
                            <dd class="col-sm-8">{{ $jawaban->penilaian->feedback_ai ?? '-' }}</dd>
                        @endif
                    </dl>
                    <hr>
                    <form action="{{ route('dosen.penilaian.store-grade', $jawaban) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Simpan Penilaian</button>
                        <a href="{{ route('dosen.penilaian.tugas', $jawaban->tugas) }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 