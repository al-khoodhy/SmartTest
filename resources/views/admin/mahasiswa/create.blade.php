@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Tambah Mahasiswa Baru</h4>
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
                    <form method="POST" action="{{ route('admin.mahasiswa.store') }}">
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
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Mahasiswa</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 