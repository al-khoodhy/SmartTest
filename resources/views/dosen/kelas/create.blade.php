@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Tambah Kelas Baru</h4>
                    <a href="{{ route('dosen.kelas.index') }}" class="btn btn-secondary">
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

                    <form method="POST" action="{{ route('dosen.kelas.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="nama_kelas">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" 
                                   id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" required>
                            @error('nama_kelas')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Contoh: Kelas A, Kelas B, dll.</small>
                        </div>

                        <div class="form-group">
                            <label for="mata_kuliah_id">Mata Kuliah <span class="text-danger">*</span></label>
                            <select class="form-control @error('mata_kuliah_id') is-invalid @enderror" id="mata_kuliah_id" name="mata_kuliah_id" required>
                                <option value="">Pilih Mata Kuliah</option>
                                @foreach($mataKuliah as $mk)
                                    <option value="{{ $mk->id }}" {{ old('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                                        {{ $mk->kode_mk }} - {{ $mk->nama_mk }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mata_kuliah_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Kelas
                            </button>
                            <a href="{{ route('dosen.kelas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 