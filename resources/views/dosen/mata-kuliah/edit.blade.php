@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Edit Mata Kuliah: {{ $mataKuliah->nama_mk }}</h4>
                    <a href="{{ route('dosen.mata-kuliah.show', $mataKuliah) }}" class="btn btn-secondary">
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

                    <form method="POST" action="{{ route('dosen.mata-kuliah.update', $mataKuliah) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="kode_mk">Kode Mata Kuliah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_mk') is-invalid @enderror" 
                                   id="kode_mk" name="kode_mk" value="{{ old('kode_mk', $mataKuliah->kode_mk) }}" required>
                            @error('kode_mk')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nama_mk">Nama Mata Kuliah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_mk') is-invalid @enderror" 
                                   id="nama_mk" name="nama_mk" value="{{ old('nama_mk', $mataKuliah->nama_mk) }}" required>
                            @error('nama_mk')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="sks">SKS <span class="text-danger">*</span></label>
                            <select class="form-control @error('sks') is-invalid @enderror" id="sks" name="sks" required>
                                <option value="">Pilih SKS</option>
                                @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ old('sks', $mataKuliah->sks) == $i ? 'selected' : '' }}>{{ $i }} SKS</option>
                                @endfor
                            </select>
                            @error('sks')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $mataKuliah->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                       {{ old('is_active', $mataKuliah->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Mata kuliah aktif
                                </label>
                            </div>
                            <small class="form-text text-muted">Jika tidak dicentang, mata kuliah akan dinonaktifkan.</small>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Mata Kuliah
                            </button>
                            <a href="{{ route('dosen.mata-kuliah.show', $mataKuliah) }}" class="btn btn-secondary">
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