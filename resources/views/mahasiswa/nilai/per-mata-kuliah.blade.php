@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Rekap Nilai per Mata Kuliah</h4>
                </div>
                <div class="card-body">
                    @if(!empty($nilaiPerMK) && count($nilaiPerMK) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>Total Tugas</th>
                                        <th>Rata-rata</th>
                                        <th>Nilai Tertinggi</th>
                                        <th>Nilai Terendah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nilaiPerMK as $item)
                                        <tr>
                                            <td>{{ $item['mata_kuliah']->nama_mk }}</td>
                                            <td>{{ $item['total_tugas'] }}</td>
                                            <td>{{ $item['rata_rata'] }}</td>
                                            <td>{{ $item['nilai_tertinggi'] }}</td>
                                            <td>{{ $item['nilai_terendah'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-x fa-3x text-muted mb-3"></i>
                            <h5>Belum ada rekap nilai</h5>
                            <p class="text-muted">Nilai per mata kuliah akan muncul di sini setelah Anda mendapatkan penilaian.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 