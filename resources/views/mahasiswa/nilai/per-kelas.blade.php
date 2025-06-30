@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Nilai Per Mata Kuliah</h4>
                    <a href="{{ route('mahasiswa.nilai.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Nilai
                    </a>
                </div>
                <div class="card-body">
                    @if(count($nilaiPerMK) > 0)
                        <div class="row">
                            @foreach($nilaiPerMK as $nilai)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ $nilai['kelas']->kelas->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</h5>
                                        <small class="text-muted">{{ $nilai['kelas']->kelas->nama_kelas ?? 'Kelas' }}</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <div class="border rounded p-2">
                                                    <h4 class="text-primary mb-0">{{ $nilai['total_tugas'] }}</h4>
                                                    <small>Total Tugas</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border rounded p-2">
                                                    <h4 class="text-success mb-0">{{ $nilai['rata_rata'] }}</h4>
                                                    <small>Rata-rata</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border rounded p-2">
                                                    <h4 class="text-info mb-0">{{ $nilai['nilai_tertinggi'] }}</h4>
                                                    <small>Tertinggi</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center">
                                            <div class="border rounded p-2">
                                                <h6 class="text-warning mb-0">{{ $nilai['nilai_terendah'] }}</h6>
                                                <small>Nilai Terendah</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x fa-3x text-muted mb-3"></i>
                            <h5>Belum ada nilai per mata kuliah</h5>
                            <p class="text-muted">Nilai tugas/ujian Anda akan dikelompokkan per mata kuliah di sini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 