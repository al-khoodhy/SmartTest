@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Tambah Dosen, Mata Kuliah, dan Kelas</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
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
                    <form method="POST" action="{{ route('admin.dosen.store') }}">
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
                        <button type="submit" class="btn btn-primary">Simpan Dosen & Kelas</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
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
                btn.closest('.kelas-item').remove();
                updateRemoveButtons();
            };
        });
        // Sembunyikan tombol hapus jika hanya satu kelas
        if(document.querySelectorAll('.kelas-item').length === 1) {
            document.querySelector('.remove-kelas').style.display = 'none';
        }
    }
    updateRemoveButtons();
});
</script>
@endsection 