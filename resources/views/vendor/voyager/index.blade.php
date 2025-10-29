@extends('voyager::master')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@php
$user = auth()->user();
$roleName = optional($user->role)->name;
$isAdmin = $roleName === 'admin';
$isDosen = $roleName === 'dosen';
// Avatar URL
$avatar = $user->avatar ?? null;
if ($avatar && \Illuminate\Support\Str::startsWith($avatar, ['http://','https://'])) {
$avatarUrl = $avatar;
} elseif ($avatar) {
$avatarUrl = asset('storage/'.ltrim($avatar,'/'));
} else {
$avatarUrl = asset('storage/users/default.png');
}
@endphp

@section('content')
<div class="container-fluid py-4">
<div class="row mb-4">
<div class="col-12">
<div class="card shadow-sm border-0">
<div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between">
<div class="d-flex align-items-center gap-3">
<img src="{{ $avatarUrl }}" onerror="this.onerror=null;this.src='{{ asset('storage/users/default.png') }}';" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;border:2px solid #e9ecef;" alt="Avatar">
<div>
<h2 class="mb-1">Halo, {{ $user->name }}</h2>
<p class="mb-0 text-muted">{{ $isAdmin ? 'Administrator' : ($isDosen ? 'Dosen' : 'Pengguna') }} • Selamat datang di panel</p>
</div>
</div>
<div class="d-none d-md-block">
<i class="bi bi-speedometer2 text-primary" style="font-size:3rem"></i>
</div>
</div>
</div>
</div>
</div>

@if($isAdmin)
<div class="row g-4">
<div class="col-md-3 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-people fs-1 text-primary"></i></div>
<h4 class="mb-0">{{ \App\Models\User::count() }}</h4>
<small class="text-muted">Total Pengguna</small>
</div>
</div>
</div>
<div class="col-md-3 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-person-badge fs-1 text-success"></i></div>
<h4 class="mb-0">{{ \App\Models\User::whereHas('role', fn($q)=>$q->where('name','dosen'))->count() }}</h4>
<small class="text-muted">Total Dosen</small>
</div>
</div>
</div>
<div class="col-md-3 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-mortarboard fs-1 text-warning"></i></div>
<h4 class="mb-0">{{ \App\Models\User::whereHas('role', fn($q)=>$q->where('name','mahasiswa'))->count() }}</h4>
<small class="text-muted">Total Mahasiswa</small>
</div>
</div>
</div>
<div class="col-md-3 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-journal-text fs-1 text-info"></i></div>
<h4 class="mb-0">{{ \App\Models\Tugas::count() }}</h4>
<small class="text-muted">Total Tugas</small>
</div>
</div>
</div>
<div class="col-md-3 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-collection fs-1 text-secondary"></i></div>
<h4 class="mb-0">{{ \App\Models\Kelas::count() }}</h4>
<small class="text-muted">Total Kelas</small>
</div>
</div>
</div>
<div class="col-md-3 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-book fs-1 text-danger"></i></div>
<h4 class="mb-0">{{ \App\Models\MataKuliah::count() }}</h4>
<small class="text-muted">Total Mata Kuliah</small>
</div>
</div>
</div>
</div>

<div class="row mt-4">
<div class="col-lg-6 mb-4">
<div class="card shadow-sm border-0 h-100">
<div class="card-header bg-white fw-semibold">Tindakan Cepat</div>
<div class="card-body">
<div class="row g-2">
<div class="col-6">
<a href="{{ route('voyager.users.index') }}" class="btn btn-light w-100 text-start"><i class="bi bi-people me-2"></i> Kelola Pengguna</a>
</div>
<div class="col-6">
<a href="{{ route('admin.dosen.create') }}" class="btn btn-light w-100 text-start"><i class="bi bi-person-badge me-2"></i> Tambah Dosen</a>
</div>
<div class="col-6">
<a href="{{ route('admin.mahasiswa.create') }}" class="btn btn-light w-100 text-start"><i class="bi bi-mortarboard me-2"></i> Tambah Mahasiswa</a>
</div>
<div class="col-6">
<a href="{{ route('voyager.bread.index') }}" class="btn btn-light w-100 text-start"><i class="bi bi-gear me-2"></i> Pengaturan BREAD</a>
</div>
</div>
</div>
</div>
</div>
<div class="col-lg-6 mb-4">
<div class="card shadow-sm border-0 h-100">
<div class="card-header bg-white fw-semibold">Tips Admin</div>
<div class="card-body">
<ul class="mb-0">
<li>Gunakan menu di sidebar untuk mengelola data pengguna, kelas, tugas, dan lainnya.</li>
<li>Pastikan data pengguna selalu terupdate untuk kelancaran sistem.</li>
<li>Gunakan fitur pencarian dan filter untuk memudahkan pengelolaan data.</li>
<li>Hubungi developer jika menemukan bug atau membutuhkan fitur baru.</li>
</ul>
</div>
</div>
</div>
</div>
@elseif($isDosen)
<div class="row g-4">
<div class="col-md-4 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-collection fs-1 text-primary"></i></div>
<h4 class="mb-0">{{ $user->kelasAsDosen()->count() }}</h4>
<small class="text-muted">Kelas Diampu</small>
</div>
</div>
</div>
<div class="col-md-4 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-book fs-1 text-success"></i></div>
<h4 class="mb-0">{{ $user->mataKuliahDiampu()->count() }}</h4>
<small class="text-muted">Mata Kuliah</small>
</div>
</div>
</div>
<div class="col-md-4 col-6">
<div class="card text-center shadow-sm border-0">
<div class="card-body">
<div class="mb-2"><i class="bi bi-journal-text fs-1 text-warning"></i></div>
<h4 class="mb-0">{{ $user->tugasDibuat()->count() }}</h4>
<small class="text-muted">Tugas Dibuat</small>
</div>
</div>
</div>
</div>

<div class="row mt-4">
<div class="col-lg-6 mb-4">
<div class="card shadow-sm border-0 h-100">
<div class="card-header bg-white fw-semibold">Tindakan Cepat</div>
<div class="card-body">
<div class="d-grid gap-2">
<a href="{{ route('dosen.dashboard') }}" class="btn btn-light text-start"><i class="bi bi-speedometer2 me-2"></i>Dashboard Dosen</a>
<a href="{{ route('dosen.tugas.index') }}" class="btn btn-light text-start"><i class="bi bi-journal-text me-2"></i>Kelola Tugas</a>
<a href="{{ route('dosen.penilaian.index') }}" class="btn btn-light text-start"><i class="bi bi-clipboard-check me-2"></i>Penilaian</a>
</div>
</div>
</div>
</div>
<div class="col-lg-6 mb-4">
<div class="card shadow-sm border-0 h-100">
<div class="card-header bg-white fw-semibold">Informasi</div>
<div class="card-body">
<p class="mb-2">Gunakan panel ini untuk akses cepat ke fitur yang sering digunakan.</p>
<ul class="mb-0">
<li>Anda dapat membuat dan mengelola tugas melalui menu Tugas.</li>
<li>Fitur Penilaian membantu mempercepat proses koreksi.</li>
</ul>
</div>
</div>
</div>
</div>
@else
<div class="alert alert-info">Anda tidak memiliki akses admin penuh. Silakan gunakan menu yang tersedia.</div>
@endif
</div>
@endsection