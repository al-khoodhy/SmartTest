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
                    <!-- Filter Form -->
                    <form class="mb-4" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="kelas_id">Mata Kuliah/Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-control">
                                    <option value="">Semua</option>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->mataKuliah->nama_mk ?? '-' }} - {{ $kelas->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Semua</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('dosen.tugas.index') }}" class="btn btn-secondary">Reset</a>
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
                                            <td>{{ $t->kelas->mataKuliah->nama_mk }}</td>
                                            <td>
                                                {{ $t->deadline->format('d/m/Y H:i') }}
                                                <br>
                                                <small class="text-muted">{{ $t->deadline->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if($t->status == 'active')
                                                    <span class="badge bg-success text-light">Aktif</span>
                                                @elseif($t->status == 'expired')
                                                    <span class="badge bg-danger text-light">Expired</span>
                                                @else
                                                    <span class="badge bg-secondary text-light">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($t->auto_grade)
                                                    <span class="badge bg-info text-dark">Ya</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Tidak</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary text-light" style="font-size:1em; min-width:2.5em;">{{ $t->jawabanMahasiswa->count() }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center align-items-center gap-1 flex-wrap" style="min-width:180px;">
                                                    <a href="{{ route('dosen.tugas.show', $t) }}" class="btn btn-sm btn-info d-flex align-items-center justify-content-center" style="min-width:36px; min-height:36px;" title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('dosen.tugas.edit', $t) }}" class="btn btn-sm btn-warning d-flex align-items-center justify-content-center" style="min-width:36px; min-height:36px;" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <form action="{{ route('dosen.tugas.toggle-status', $t) }}" method="POST" class="m-0 p-0 d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="button" class="btn btn-sm {{ $t->is_active ? 'btn-secondary' : 'btn-success' }} btn-toggle-status d-flex align-items-center justify-content-center" style="min-width:36px; min-height:36px;" title="Ubah Status">
                                                            <i class="bi bi-{{ $t->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                                        </button>
                                                    </form>
                                                    @if($t->jawabanMahasiswa->count() == 0)
                                                    <form action="{{ route('dosen.tugas.destroy', $t) }}" method="POST" class="m-0 p-0 d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger btn-delete-tugas d-flex align-items-center justify-content-center" style="min-width:36px; min-height:36px;" title="Hapus">
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

<!-- Modal Konfirmasi Khusus Halaman Ini (warna dinamis sesuai aksi) -->
<div class="modal fade" id="localConfirmModal" tabindex="-1" aria-labelledby="localConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" id="localConfirmModalHeader">
        <h5 class="modal-title" id="localConfirmModalLabel">Konfirmasi</h5>
      </div>
      <div class="modal-body" id="localConfirmModalBody">
        <!-- Pesan konfirmasi -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn" id="localConfirmModalYes">Ya</button>
      </div>
    </div>
  </div>
</div>
@endsection

<script>
(function() {
    let localConfirmAction = null;
    function showLocalConfirmModal(message, action, type) {
        var body = document.getElementById('localConfirmModalBody');
        var header = document.getElementById('localConfirmModalHeader');
        var yesBtn = document.getElementById('localConfirmModalYes');
        if (body) body.textContent = message;
        // Reset warna
        header.className = 'modal-header';
        header.classList.remove('bg-danger', 'bg-primary', 'bg-secondary', 'text-white');
        yesBtn.className = 'btn';
        yesBtn.classList.remove('btn-danger', 'btn-primary', 'btn-secondary');
        if (type === 'hapus') {
            header.classList.add('bg-danger', 'text-white');
            yesBtn.classList.add('btn-danger');
            yesBtn.textContent = 'Ya, Hapus';
        } else if (type === 'status') {
            header.classList.add('bg-primary', 'text-white');
            yesBtn.classList.add('btn-primary');
            yesBtn.textContent = 'Ya, Ubah Status';
        } else {
            header.classList.add('bg-secondary', 'text-white');
            yesBtn.classList.add('btn-secondary');
            yesBtn.textContent = 'Ya';
        }
        localConfirmAction = action;
        var modalEl = document.getElementById('localConfirmModal');
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
            window._currentLocalConfirmModal = modal;
        }
    }
    var yesBtn = document.getElementById('localConfirmModalYes');
    if (yesBtn) {
        yesBtn.onclick = function() {
            if (localConfirmAction) localConfirmAction();
            if (window._currentLocalConfirmModal) window._currentLocalConfirmModal.hide();
        };
    }
    document.addEventListener('click', function(e) {
        // Ubah status
        if (e.target.closest('.btn-toggle-status')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-toggle-status');
            const form = btn.closest('form');
            showLocalConfirmModal('Yakin ingin mengubah status tugas?', function() {
                form.submit();
            }, 'status');
        }
        // Hapus tugas
        if (e.target.closest('.btn-delete-tugas')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-delete-tugas');
            const form = btn.closest('form');
            showLocalConfirmModal('Yakin ingin menghapus tugas ini?', function() {
                form.submit();
            }, 'hapus');
        }
    });
})();
</script>

