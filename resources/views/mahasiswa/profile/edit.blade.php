@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit Profil</div>
                <div class="card-body">
                    <form action="{{ route('mahasiswa.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Profil</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                @php
                                    $avatar = $user->avatar ?? null;
                                    if ($avatar && \Illuminate\Support\Str::startsWith($avatar, ['http://','https://'])) {
                                        $avatarUrl = $avatar;
                                    } elseif ($avatar) {
                                        $avatarUrl = asset('storage/'.ltrim($avatar,'/'));
                                    } else {
                                        $avatarUrl = asset('storage/users/default.png');
                                    }
                                @endphp
                                <img src="{{ $avatarUrl }}" alt="Foto Akun" class="rounded-circle" style="width:60px; height:60px; object-fit:cover; border:2px solid #e9ecef;" onerror="this.onerror=null;this.src='{{ asset('storage/users/default.png') }}';">
                                <input type="file" class="form-control @error('avatar') is-invalid @enderror" name="avatar" accept="image/*">
                            </div>
                            <div class="form-text">Format: JPG, JPEG, PNG, WEBP. Maks 2 MB.</div>
                            @error('avatar')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('mahasiswa.profile.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


