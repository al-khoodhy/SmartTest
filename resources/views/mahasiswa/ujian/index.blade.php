@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">🎓 Tidak Ada Ujian yang Sedang Berlangsung</h3>
                    <p class="text-muted fs-5">
                       Saat ini Anda belum memiliki ujian aktif.<br>
                        Silakan periksa kembali jadwal ujian pada menu di bawah<br>
                        atau hubungi dosen pengampu jika seharusnya ada ujian hari ini.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-primary rounded-pill px-4 py-2">
                            <i class="bi bi-house-door"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
