@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Edit Tugas</h4>
                    <a href="{{ route('dosen.tugas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('dosen.tugas.update', $tugas) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="judul">Judul Tugas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                           id="judul" name="judul" value="{{ old('judul', $tugas->judul) }}" required>
                                    @error('judul')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mata_kuliah_id">Mata Kuliah <span class="text-danger">*</span></label>
                                    <select class="form-control @error('mata_kuliah_id') is-invalid @enderror" 
                                            id="mata_kuliah_id" name="mata_kuliah_id" required>
                                        <option value="">Pilih Mata Kuliah</option>
                                        @foreach($mataKuliah as $mk)
                                            <option value="{{ $mk->id }}" {{ old('mata_kuliah_id', $tugas->mata_kuliah_id) == $mk->id ? 'selected' : '' }}>
                                                {{ $mk->nama_mk }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mata_kuliah_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Tugas <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="3" required>{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Soal & Bobot <span class="text-danger">*</span></label>
                            <div id="soal-list">
                                @foreach($tugas->soal as $i => $soal)
                                <div class="soal-item row mb-2">
                                    <div class="col-md-9">
                                        <input type="text" name="soal[{{ $i }}][pertanyaan]" class="form-control" value="{{ old('soal.'.$i.'.pertanyaan', $soal->pertanyaan) }}" placeholder="Tulis pertanyaan soal" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="soal[{{ $i }}][bobot]" class="form-control" value="{{ old('soal.'.$i.'.bobot', $soal->bobot) }}" placeholder="Bobot" min="0.01" step="0.01" required>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-soal">&times;</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="add-soal">Tambah Soal</button>
                        </div>

                        <div class="form-group">
                            <label for="rubrik_penilaian">Rubrik Penilaian</label>
                            <textarea class="form-control @error('rubrik_penilaian') is-invalid @enderror" 
                                      id="rubrik_penilaian" name="rubrik_penilaian" rows="4"
                                      placeholder="Contoh: 1) Pemahaman konsep (40%), 2) Analisis (30%), 3) Struktur penulisan (30%)">{{ old('rubrik_penilaian', $tugas->rubrik_penilaian) }}</textarea>
                            @error('rubrik_penilaian')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Rubrik ini akan digunakan oleh AI untuk penilaian otomatis.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="deadline">Deadline <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('deadline') is-invalid @enderror" 
                                           id="deadline" name="deadline" value="{{ old('deadline', $tugas->deadline->format('Y-m-d\TH:i')) }}" required>
                                    @error('deadline')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="durasi_menit">Durasi (Menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror" 
                                           id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit', $tugas->durasi_menit) }}" 
                                           min="30" max="480" required>
                                    @error('durasi_menit')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_maksimal">Nilai Maksimal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('nilai_maksimal') is-invalid @enderror" 
                                           id="nilai_maksimal" name="nilai_maksimal" value="{{ old('nilai_maksimal', $tugas->nilai_maksimal) }}" 
                                           min="1" max="100" required>
                                    @error('nilai_maksimal')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="auto_grade" name="auto_grade" 
                                       {{ old('auto_grade', $tugas->auto_grade) ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_grade">
                                    Aktifkan Penilaian Otomatis dengan AI
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Jika diaktifkan, jawaban mahasiswa akan dinilai otomatis menggunakan AI Gemini.
                            </small>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Tugas
                            </button>
                            <a href="{{ route('dosen.tugas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum datetime to current time
    const deadlineInput = document.getElementById('deadline');
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    deadlineInput.min = now.toISOString().slice(0, 16);

    // Dynamic soal
    let soalIndex = {{ count($tugas->soal) }};
    document.getElementById('add-soal').onclick = function() {
        const soalList = document.getElementById('soal-list');
        const newItem = document.createElement('div');
        newItem.className = 'soal-item row mb-2';
        newItem.innerHTML = `
            <div class="col-md-9">
                <input type="text" name="soal[${soalIndex}][pertanyaan]" class="form-control" placeholder="Tulis pertanyaan soal" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="soal[${soalIndex}][bobot]" class="form-control" placeholder="Bobot" min="0.01" step="0.01" required>
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-danger btn-sm remove-soal">&times;</button>
            </div>
        `;
        soalList.appendChild(newItem);
        soalIndex++;
        updateRemoveButtons();
    };
    function updateRemoveButtons() {
        document.querySelectorAll('.remove-soal').forEach(btn => {
            btn.style.display = '';
            btn.onclick = function() {
                btn.closest('.soal-item').remove();
            };
        });
        // Sembunyikan tombol hapus jika hanya satu soal
        if(document.querySelectorAll('.soal-item').length === 1) {
            document.querySelector('.remove-soal').style.display = 'none';
        }
    }
    updateRemoveButtons();
});
</script>
@endsection 