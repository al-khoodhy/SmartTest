@extends('voyager::master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div>
                        <h2 class="mb-1">Selamat Datang di Admin Panel</h2>
                        <p class="mb-0 text-muted">Kelola data, pengguna, dan sistem SmartTest dari satu tempat.</p>
                    </div>
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin" width="80" class="d-none d-md-block">
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-3 col-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="mb-2"><i class="bi bi-people fs-1 text-primary"></i></div>
                    <h4 class="mb-0">{{ \App\Models\User::count() }}</h4>
                    <small class="text-muted">Total Pengguna</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="mb-2"><i class="bi bi-journal-text fs-1 text-success"></i></div>
                    <h4 class="mb-0">{{ \App\Models\Tugas::count() }}</h4>
                    <small class="text-muted">Total Tugas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="mb-2"><i class="bi bi-people-fill fs-1 text-warning"></i></div>
                    <h4 class="mb-0">{{ \App\Models\Kelas::count() }}</h4>
                    <small class="text-muted">Total Kelas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="mb-2"><i class="bi bi-book fs-1 text-info"></i></div>
                    <h4 class="mb-0">{{ \App\Models\MataKuliah::count() }}</h4>
                    <small class="text-muted">Total Mata Kuliah</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Tips Admin</h5>
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
</div>
@endsection 