@extends('voyager::master')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-person"></i> Tambah Mahasiswa Baru
        </h1>
        <!-- Tombol Import CSV -->
        <button type="button" class="btn btn-info mb-3" id="toggle-import-csv">Import Mahasiswa via CSV</button>
    </div>
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    @include('voyager::alerts')
                    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Import Mahasiswa via CSV (hidden by default) -->
                    <div class="mb-4" id="import-csv-form" style="display:none;">
                        <h4>Import Mahasiswa via CSV</h4>
                        <form method="POST" action="{{ route('admin.mahasiswa.import') }}" enctype="multipart/form-data" onsubmit="return confirm('Yakin ingin mengimport data mahasiswa dari file ini?')">
                            @csrf
                            <div class="form-group h-2">
                                <label for="csv_file">Pilih File CSV</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                                <small class="form-text text-muted">Format: nama,email,nim,password (dengan header)</small>
                            </div>
                            <div class="form-group">
                                <label>Pilih Kelas untuk Semua Mahasiswa</label>
                                <select name="kelas_ids[]" class="form-control" multiple required>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }} ({{ $kelas->mataKuliah->nama_mk ?? '-' }})</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Semua mahasiswa di file CSV akan dimasukkan ke kelas yang dipilih di sini.</small>
                            </div>
                            <button type="submit" class="btn btn-success">Import Mahasiswa</button>
                        </form>
                        <hr>
                    </div>

                    <!-- Form Manual -->
                    <div id="manual-form">
                        <form method="POST" action="{{ route('admin.mahasiswa.store') }}" id="formTambahMahasiswa">
                            @csrf
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                            </div>
                            <div class="form-group">
                                <label>Email (unik)</label>
                                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                            </div>
                            <div class="form-group">
                                <label>NIM (unik)</label>
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
                            <div class="form-group">
                                <label>Pilih Kelas</label>
                                <select name="kelas_ids[]" class="form-control" multiple required>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" {{ (collect(old('kelas_ids'))->contains($kelas->id)) ? 'selected' : '' }}>{{ $kelas->nama_kelas }} ({{ $kelas->mataKuliah->nama_mk ?? '-' }})</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Pilih satu atau lebih kelas yang akan diikuti mahasiswa ini</small>
                            </div>
                            {{-- <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div> --}}
                            <button type="submit" class="btn btn-primary" id="btnSimpanMahasiswa">Simpan Mahasiswa</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Voyager Style -->
<div class="modal fade" id="voyagerConfirmModal" tabindex="-1" role="dialog" aria-labelledby="voyagerConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="voyagerConfirmModalLabel"><i class="voyager-person"></i> Konfirmasi Simpan Mahasiswa</h5>
        {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> --}}
      </div>
      <div class="modal-body" id="voyagerConfirmModalBody">
        Yakin ingin menyimpan data mahasiswa ini?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="voyagerConfirmModalYes">Ya, Simpan</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('javascript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('toggle-import-csv');
    var importForm = document.getElementById('import-csv-form');
    var manualForm = document.getElementById('manual-form');
    if(btn && importForm && manualForm) {
        btn.addEventListener('click', function() {
            if(importForm.style.display === 'none') {
                importForm.style.display = '';
                manualForm.style.display = 'none';
                btn.textContent = 'Tutup Form Import CSV';
            } else {
                importForm.style.display = 'none';
                manualForm.style.display = '';
                btn.textContent = 'Import Mahasiswa via CSV';
            }
        });
    }

    // Voyager style confirmation modal untuk submit mahasiswa
    var voyagerModal = $('#voyagerConfirmModal');
    var voyagerModalYes = document.getElementById('voyagerConfirmModalYes');
    var formTambahMahasiswa = document.getElementById('formTambahMahasiswa');
    var btnSimpanMahasiswa = document.getElementById('btnSimpanMahasiswa');
    var mahasiswaFormToSubmit = null;
    if(formTambahMahasiswa && btnSimpanMahasiswa) {
        btnSimpanMahasiswa.addEventListener('click', function(e) {
            e.preventDefault();
            mahasiswaFormToSubmit = formTambahMahasiswa;
            voyagerModal.modal('show');
        });
    }
    voyagerModalYes.onclick = function() {
        if(mahasiswaFormToSubmit) {
            mahasiswaFormToSubmit.submit();
            mahasiswaFormToSubmit = null;
            voyagerModal.modal('hide');
        }
    };
});
</script>
@stop 