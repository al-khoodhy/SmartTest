@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Kelola Tugas</h4>
                    <a href="{{ route('dosen.tugas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Tugas Baru
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="mata_kuliah_id" class="form-control">
                                    <option value="">Semua Mata Kuliah</option>
                                    @foreach($mataKuliah as $mk)
                                        <option value="{{ $mk->id }}" {{ request('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                                            {{ $mk->nama_mk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('dosen.tugas.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Tugas List -->
                    @if($tugas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Mata Kuliah</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th>Auto Grade</th>
                                        <th>Jawaban</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tugas as $t)
                                        <tr>
                                            <td>
                                                <strong>{{ $t->judul }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Str::limit($t->deskripsi, 50) }}</small>
                                            </td>
                                            <td>{{ $t->mataKuliah->nama_mk }}</td>
                                            <td>
                                                {{ $t->deadline->format('d/m/Y H:i') }}
                                                <br>
                                                <small class="text-muted">{{ $t->deadline->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if($t->status == 'active')
                                                    <span class="badge badge-success">Aktif</span>
                                                @elseif($t->status == 'expired')
                                                    <span class="badge badge-danger">Expired</span>
                                                @else
                                                    <span class="badge badge-secondary">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($t->auto_grade)
                                                    <span class="badge badge-info">Ya</span>
                                                @else
                                                    <span class="badge badge-warning">Tidak</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $t->jawabanMahasiswa->count() }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('dosen.tugas.show', $t) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('dosen.tugas.edit', $t) }}" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <form action="{{ route('dosen.tugas.toggle-status', $t) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm {{ $t->is_active ? 'btn-secondary' : 'btn-success' }}" 
                                                                onclick="return confirm('Yakin ingin mengubah status tugas?')" title="Ubah Status">
                                                            <i class="bi bi-{{ $t->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                                        </button>
                                                    </form>
                                                    @if($t->jawabanMahasiswa->count() == 0)
                                                        <form action="{{ route('dosen.tugas.destroy', $t) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('Yakin ingin menghapus tugas ini?')" title="Hapus">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $tugas->appends(request()->query())->links('vendor.pagination.simple-numeric') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5>Belum ada tugas</h5>
                            <p class="text-muted">Mulai dengan membuat tugas pertama Anda.</p>
                            <a href="{{ route('dosen.tugas.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Tugas Baru
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

