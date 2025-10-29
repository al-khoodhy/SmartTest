@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Profil Mahasiswa</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="d-flex align-items-center gap-3 mb-3">
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
                        <img src="{{ $avatarUrl }}" alt="Foto Akun" class="rounded-circle" style="width:80px; height:80px; object-fit:cover; border:2px solid #e9ecef;" onerror="this.onerror=null;this.src='{{ asset('storage/users/default.png') }}';">
                        <div>
                            <div class="fs-5 fw-semibold">{{ $user->name }}</div>
                            <div class="text-muted">{{ $user->email }}</div>
                            <div class="text-muted">NIM: {{ $user->nim_nip }}</div>
                        </div>
                    </div>
                    <a href="{{ route('mahasiswa.profile.edit') }}" class="btn btn-primary me-2">Edit Profil</a>
                    <a href="{{ route('mahasiswa.profile.change-password') }}" class="btn btn-outline-secondary">Ganti Password</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


