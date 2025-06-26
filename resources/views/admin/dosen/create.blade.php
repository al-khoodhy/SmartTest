@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Pendaftaran Dosen & Mata Kuliah</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.dosen.store') }}">
        @csrf
        <div class="form-group mb-2">
            <label for="name">Nama Dosen</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group mb-2">
            <label for="email">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group mb-2">
            <label for="nim_nip">NIP</label>
            <input type="text" class="form-control @error('nim_nip') is-invalid @enderror" id="nim_nip" name="nim_nip" value="{{ old('nim_nip') }}" required>
            @error('nim_nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group mb-2">
            <label for="password">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group mb-2">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
        <hr>
        <div class="mb-3">
            <p class="mb-1"><strong>Atau tambah mata kuliah baru (otomatis direlasikan ke dosen ini):</strong></p>
            <div class="form-group mb-2">
                <label for="nama_mk">Nama Mata Kuliah Baru</label>
                <input type="text" class="form-control @error('nama_mk') is-invalid @enderror" id="nama_mk" name="nama_mk" value="{{ old('nama_mk') }}">
                @error('nama_mk')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group mb-2">
                <label for="kode_mk">Kode Mata Kuliah Baru</label>
                <input type="text" class="form-control @error('kode_mk') is-invalid @enderror" id="kode_mk" name="kode_mk" value="{{ old('kode_mk') }}">
                @error('kode_mk')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Daftarkan Dosen</button>
    </form>
</div>
@endsection 