@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Buat Tugas Baru</h4>
                    <a href="{{ route('dosen.tugas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('dosen.tugas.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="judul">Judul Tugas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                           id="judul" name="judul" value="{{ old('judul') }}" required>
                                    @error('judul')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="kelas_id">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kelas_id') is-invalid @enderror" id="kelas_id" name="kelas_id" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Tugas <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="3" required>{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Soal & Bobot <span class="text-danger">*</span></label>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="gunakanKunciJawaban" name="gunakan_kunci_jawaban" value="1" {{ old('gunakan_kunci_jawaban') ? 'checked' : '' }}>
                                <label class="form-check-label" for="gunakanKunciJawaban">Gunakan Kunci Jawaban</label>
                            </div>
                            <div id="soal-list">
                                <div class="soal-item row mb-2">
                                    <div class="col-md-6">
                                        <input type="text" name="soal[0][pertanyaan]" class="form-control" placeholder="Tulis pertanyaan soal" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="soal[0][bobot]" class="form-control" placeholder="Bobot" min="0.01" step="0.01" required>
                                    </div>
                                    <div class="col-md-3 kunci-jawaban-wrap" style="display:none;">
                                        <input type="text" name="soal[0][kunci_jawaban]" class="form-control" placeholder="Kunci jawaban">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-soal" style="display:none">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="add-soal">Tambah Soal</button>
                        </div>

                        <div class="form-group">
                            <label for="rubrik_penilaian">Rubrik Penilaian</label>
                            <textarea class="form-control @error('rubrik_penilaian') is-invalid @enderror" 
                                      id="rubrik_penilaian" name="rubrik_penilaian" rows="4" 
                                      placeholder="Contoh: K1_konten (0.35), K2_argumentasi (0.25), K3_struktur (0.20), K4_istilah (0.15), K5_bahasa (0.05)">{{ old('rubrik_penilaian') }}</textarea>
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
                                           id="deadline" name="deadline" value="{{ old('deadline') }}" required>
                                    @error('deadline')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="durasi_menit">Durasi (Menit) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror" 
                                           id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit', 120) }}" 
                                           min="1" max="480" required>
                                    @error('durasi_menit')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_maksimal">Nilai Maksimal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('nilai_maksimal') is-invalid @enderror" 
                                           id="nilai_maksimal" name="nilai_maksimal" value="{{ old('nilai_maksimal', 100) }}" 
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
                                       {{ old('auto_grade') ? 'checked' : '' }}>
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
                            <button type="submit" class="btn btn-primary" id="btnSimpanTugas">
                                <i class="fas fa-save"></i> Simpan Tugas
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

<!-- Modal Konfirmasi Voyager Style -->
<div class="modal fade" id="voyagerConfirmModal" tabindex="-1" role="dialog" aria-labelledby="voyagerConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="voyagerConfirmModalLabel"><i class="voyager-warning"></i> Konfirmasi</h5>
        {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> --}}
      </div>
      <div class="modal-body" id="voyagerConfirmModalBody">
        Apakah Anda yakin?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="voyagerConfirmModalYes">Ya</button>
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
    let soalIndex = 1;
    document.getElementById('add-soal').onclick = function() {
        const soalList = document.getElementById('soal-list');
        const newItem = document.createElement('div');
        newItem.className = 'soal-item row mb-2';
        newItem.innerHTML = `
            <div class="col-md-6">
                <input type="text" name="soal[${soalIndex}][pertanyaan]" class="form-control" placeholder="Tulis pertanyaan soal" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="soal[${soalIndex}][bobot]" class="form-control" placeholder="Bobot" min="0.01" step="0.01" required>
            </div>
            <div class="col-md-3 kunci-jawaban-wrap" style="display:none;">
                <input type="text" name="soal[${soalIndex}][kunci_jawaban]" class="form-control" placeholder="Kunci jawaban">
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-danger btn-sm remove-soal">&times;</button>
            </div>
        `;
        soalList.appendChild(newItem);
        soalIndex++;
        updateRemoveButtons();
        updateKunciVisibility();
    };
    function updateRemoveButtons() {
        document.querySelectorAll('.remove-soal').forEach(btn => {
            btn.style.display = '';
            btn.onclick = function() {
                if (window.confirm('Yakin ingin menghapus soal ini?')) {
                    btn.closest('.soal-item').remove();
                    updateRemoveButtons();
                }
            };
        });
        // Sembunyikan tombol hapus jika hanya satu soal
        if(document.querySelectorAll('.soal-item').length === 1) {
            document.querySelector('.remove-soal').style.display = 'none';
        }
    }
    updateRemoveButtons();

    function updateKunciVisibility() {
        let show = document.getElementById('gunakanKunciJawaban').checked;
        document.querySelectorAll('.kunci-jawaban-wrap').forEach(el => {
            el.style.display = show ? '' : 'none';
        });
    }
    document.getElementById('gunakanKunciJawaban').addEventListener('change', updateKunciVisibility);
    // Pastikan juga update saat load page
    updateKunciVisibility();

    // Voyager style confirmation modal untuk submit tugas
    var voyagerModal = $('#voyagerConfirmModal');
    var voyagerModalBody = document.getElementById('voyagerConfirmModalBody');
    var voyagerModalYes = document.getElementById('voyagerConfirmModalYes');
    var tugasForm = document.querySelector('form[action*="tugas/store"]');
    var tugasFormToSubmit = null;
    function showVoyagerConfirm(message, form) {
        voyagerModalBody.textContent = message;
        tugasFormToSubmit = form;
        voyagerModal.modal('show');
    }
    voyagerModalYes.onclick = function() {
        if(tugasFormToSubmit) {
            tugasFormToSubmit.submit();
            tugasFormToSubmit = null;
            voyagerModal.modal('hide');
        }
    };
    var btnSimpanTugas = document.getElementById('btnSimpanTugas');
    if(tugasForm && btnSimpanTugas) {
        btnSimpanTugas.addEventListener('click', function(e) {
            e.preventDefault();
            showVoyagerConfirm('Yakin ingin menyimpan tugas ini?', tugasForm);
        });
    }
});
</script>
@endsection

