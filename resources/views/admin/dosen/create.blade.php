@extends('voyager::master')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-person"></i> Tambah Dosen, Mata Kuliah, dan Kelas
        </h1>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        @include('voyager::alerts')
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('admin.dosen.store') }}" onsubmit="return confirm('Yakin ingin menyimpan data dosen dan kelas ini?')">
                            @csrf
                            <h5>Data Dosen</h5>
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                            </div>
                            <div class="form-group">
                                <label>Email (unik)</label>
                                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                            </div>
                            <div class="form-group">
                                <label>NIP/NIDN/NIK (unik)</label>
                                <input type="text" name="nim_nip" class="form-control" required value="{{ old('nim_nip') }}">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <hr>
                            <h5>Mata Kuliah yang Diampu</h5>
                            <div class="form-group">
                                <label>Pilih Mata Kuliah</label>
                                <select name="mata_kuliah_id" class="form-control">
                                    <option value="">-- Pilih Mata Kuliah --</option>
                                    @foreach($mataKuliah as $mk)
                                        <option value="{{ $mk->id }}" {{ old('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>{{ $mk->kode_mk }} - {{ $mk->nama_mk }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Atau tambah mata kuliah baru di bawah ini</small>
                            </div>
                            <div class="form-group">
                                <label>Nama Mata Kuliah Baru</label>
                                <input type="text" name="nama_mk" class="form-control" value="{{ old('nama_mk') }}">
                            </div>
                            <div class="form-group">
                                <label>Kode Mata Kuliah Baru</label>
                                <input type="text" name="kode_mk" class="form-control" value="{{ old('kode_mk') }}">
                            </div>
                            <hr>
                            <h5>Kelas yang Diampu</h5>
                            <div class="form-group">
                                <label><input type="radio" name="kelas_mode" value="baru" checked> Buat Kelas Baru</label>
                                <label class="ml-3"><input type="radio" name="kelas_mode" value="pilih"> Pilih Kelas yang Sudah Ada</label>
                            </div>
                            <div id="kelas-baru-section">
                                <div id="kelas-list">
                                    <div class="kelas-item border rounded p-3 mb-3">
                                        <div class="form-group">
                                            <label>Nama Kelas</label>
                                            <input type="text" name="kelas[0][nama_kelas]" class="form-control" required>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-kelas" style="display:none">Hapus Kelas</button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-success btn-sm mb-3" id="add-kelas">Tambah Kelas</button>
                            </div>
                            <div id="kelas-pilih-section" style="display:none">
                                <div class="form-group">
                                    <label>Pilih Kelas yang Sudah Ada</label>
                                    <select name="kelas_pilih[]" class="form-control" multiple>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }} ({{ $kelas->mataKuliah->nama_mk ?? '-' }})</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Pilih satu atau lebih kelas yang sudah ada untuk diampu dosen ini.</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Dosen & Kelas</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

<!-- Modal Konfirmasi Voyager Style -->
<div class="modal fade" id="voyagerConfirmModal" tabindex="-1" role="dialog" aria-labelledby="voyagerConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="voyagerConfirmModalLabel"><i class="voyager-warning"></i> Konfirmasi</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let kelasIndex = 1;
    document.getElementById('add-kelas').onclick = function() {
        const kelasList = document.getElementById('kelas-list');
        const newItem = document.createElement('div');
        newItem.className = 'kelas-item border rounded p-3 mb-3';
        newItem.innerHTML = `
            <div class="form-group">
                <label>Nama Kelas</label>
                <input type="text" name="kelas[${kelasIndex}][nama_kelas]" class="form-control" required>
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-kelas">Hapus Kelas</button>
        `;
        kelasList.appendChild(newItem);
        kelasIndex++;
        updateRemoveButtons();
    };
    function updateRemoveButtons() {
        document.querySelectorAll('.remove-kelas').forEach(btn => {
            btn.style.display = '';
            btn.onclick = function() {
                // Voyager style confirmation for delete kelas
                showVoyagerConfirm('Yakin ingin menghapus kelas ini?', function() {
                    btn.closest('.kelas-item').remove();
                    updateRemoveButtons();
                });
            };
        });
        // Sembunyikan tombol hapus jika hanya satu kelas
        if(document.querySelectorAll('.kelas-item').length === 1) {
            document.querySelector('.remove-kelas').style.display = 'none';
        }
    }
    updateRemoveButtons();

    // Voyager style confirmation modal
    var voyagerModal = $('#voyagerConfirmModal');
    var voyagerModalBody = document.getElementById('voyagerConfirmModalBody');
    var voyagerModalYes = document.getElementById('voyagerConfirmModalYes');
    var confirmCallback = null;
    function showVoyagerConfirm(message, callback) {
        voyagerModalBody.textContent = message;
        confirmCallback = callback;
        voyagerModal.modal('show');
    }
    voyagerModalYes.onclick = function() {
        if(confirmCallback) {
            confirmCallback();
            confirmCallback = null;
            voyagerModal.modal('hide');
        }
    };

    // Attach to form submit
    var formEl = document.querySelector('form[action*="dosen/store"]');
    if(formEl) {
        formEl.addEventListener('submit', function(e) {
            e.preventDefault();
            showVoyagerConfirm('Yakin ingin menyimpan data dosen dan kelas ini?', () => formEl.submit());
        });
    }

    // Toggle kelas baru/pilih
    document.querySelectorAll('input[name="kelas_mode"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if(this.value === 'baru') {
                document.getElementById('kelas-baru-section').style.display = '';
                document.getElementById('kelas-pilih-section').style.display = 'none';
                // Set required for kelas baru
                document.querySelectorAll('#kelas-baru-section input[name^="kelas"]').forEach(i => i.required = true);
                document.querySelector('select[name="kelas_pilih[]"]').required = false;
            } else {
                document.getElementById('kelas-baru-section').style.display = 'none';
                document.getElementById('kelas-pilih-section').style.display = '';
                // Set required for kelas pilih
                document.querySelectorAll('#kelas-baru-section input[name^="kelas"]').forEach(i => i.required = false);
                document.querySelector('select[name="kelas_pilih[]"]').required = true;
            }
        });
    });
});
</script>
@stop 