@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Tugas</h4>
                    <a href="{{ route('mahasiswa.tugas.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
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
                        <dt class="col-sm-4">Deadline</dt>
                        <dd class="col-sm-8">{{ $tugas->deadline->format('d/m/Y H:i') }} ({{ $tugas->deadline->diffForHumans() }})</dd>
                        <dt class="col-sm-4">Nilai Maksimal</dt>
                        <dd class="col-sm-8">{{ $tugas->nilai_maksimal }}</dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($tugas->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                        </dd>
                    </dl>

                    @if($canWork)
                        <hr>
                        <form method="POST" action="{{ route('mahasiswa.tugas.submit', $tugas) }}">
                            @csrf
                            <div class="form-group">
                                <label for="jawaban">Jawaban Anda <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('jawaban') is-invalid @enderror" id="jawaban" name="jawaban" rows="8" required>{{ old('jawaban', $jawaban ? $jawaban->isi_jawaban : '') }}</textarea>
                                @error('jawaban')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Submit Jawaban
                                </button>
                            </div>
                        </form>
                    @elseif($jawaban)
                        <hr>
                        <h5>Jawaban Anda:</h5>
                        <ol>
                            @foreach($jawaban->jawabanSoal as $jawabanSoal)
                                <li class="mb-3">
                                    <div><strong>Pertanyaan:</strong> {{ $jawabanSoal->soal->pertanyaan ?? '-' }}</div>
                                    <div><strong>Jawaban:</strong> {!! nl2br(e($jawabanSoal->jawaban)) !!}</div>
                                    @if($jawabanSoal->penilaian)
                                        <div class="alert alert-success">
                                            <strong>Nilai: {{ $jawabanSoal->penilaian->nilai_final }}</strong>
                                            <br>Feedback: {{ $jawabanSoal->penilaian->feedback_manual ?? '-' }}
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                        @if($jawaban->status === 'graded')
                            <div class="alert alert-success">
                                <strong>Nilai Akhir (total): {{ $jawaban->nilai_akhir }}</strong>
                            </div>
                        @endif
                    @elseif($isExpired)
                        <div class="alert alert-danger mt-4">Tugas sudah expired. Anda tidak dapat mengumpulkan jawaban.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 