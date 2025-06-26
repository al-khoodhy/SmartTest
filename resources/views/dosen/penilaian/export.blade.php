@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Export Nilai Mahasiswa</h1>
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Mahasiswa</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            {{-- Contoh data statis, ganti dengan loop data nilai dari controller --}}
            <tr>
                <td>1</td>
                <td>Budi Santoso</td>
                <td>90</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Sari Dewi</td>
                <td>85</td>
            </tr>
            {{-- Akhiri contoh data --}}
        </tbody>
    </table>
    <a href="#" class="btn btn-success">Download Excel</a>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection 