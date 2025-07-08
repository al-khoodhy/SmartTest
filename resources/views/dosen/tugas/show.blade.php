@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Detail Tugas</h4>
                    <div>
                        <a href="{{ route('dosen.tugas.edit', $tugas) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('dosen.tugas.toggle-status', $tugas) }}" method="POST" style="display: inline;" id="formToggleStatus">
                            @csrf
                            @method('PATCH')
                            <button type="button" class="btn btn-{{ $tugas->is_active ? 'secondary' : 'success' }} me-2" id="btnToggleStatus">
                                <i class="fas fa-{{ $tugas->is_active ? 'pause' : 'play' }}"></i> 
                                {{ $tugas->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        @if($tugas->jawabanMahasiswa->count() == 0)
                            <form action="{{ route('dosen.tugas.destroy', $tugas) }}" method="POST" style="display: inline;" id="formDeleteTugas">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger me-2 btn-delete-tugas" id="btnDeleteTugas">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('dosen.tugas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Judul Tugas</dt>
                        <dd class="col-sm-8">{{ $tugas->judul }}</dd>

                        <dt class="col-sm-4">Mata Kuliah</dt>
                        <dd class="col-sm-8">{{ $tugas->mataKuliah->nama_mk }}</dd>

                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $tugas->deskripsi }}</dd>

                        <dt class="col-sm-4">Daftar Soal</dt>
                        <dd class="col-sm-8">
                            <ol>
                                @foreach($tugas->soal as $soal)
                                    <li>
                                        <div><strong>Pertanyaan:</strong> {{ $soal->pertanyaan }}</div>
                                        <div><strong>Bobot:</strong> {{ $soal->bobot }}</div>
                                    </li>
                                @endforeach
                            </ol>
                        </dd>

                        <dt class="col-sm-4">Rubrik Penilaian</dt>
                        <dd class="col-sm-8">{!! nl2br(e($tugas->rubrik_penilaian)) !!}</dd>

                        <dt class="col-sm-4">Deadline</dt>
                        <dd class="col-sm-8">
                            {{ $tugas->deadline->format('d/m/Y H:i') }}
                            @if($tugas->deadline->isPast())
                                <span class="text-danger">(Sudah lewat {{ $tugas->deadline->diffForHumans(null, null, true) }})</span>
                            @else
                                <span class="text-success">({{ $tugas->deadline->diffForHumans(null, null, true) }} lagi)</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Durasi (Menit)</dt>
                        <dd class="col-sm-8">{{ $tugas->durasi_menit }}</dd>

                        <dt class="col-sm-4">Nilai Maksimal</dt>
                        <dd class="col-sm-8">{{ $tugas->nilai_maksimal }}</dd>

                        <dt class="col-sm-4">Penilaian Otomatis</dt>
                        <dd class="col-sm-8">
                            @if($tugas->auto_grade)
                                <span class="badge bg-info">Ya</span>
                            @else
                                <span class="badge bg-warning text-dark">Tidak</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if($tugas->status == 'active')
                                <span class="badge bg-success">Aktif</span>
                            @elseif($tugas->status == 'expired')
                                <span class="badge bg-danger">Expired</span>
                            @else
                                <span class="badge bg-secondary">Tidak Aktif</span>
                            @endif
                        </dd>
                    </dl>
                    
                    <!-- Statistik -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Statistik Tugas</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $totalMahasiswa }}</h4>
                                            <small>Total Mahasiswa</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $sudahMengerjakan }}</h4>
                                            <small>Sudah Mengerjakan</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $sudahSubmit }}</h4>
                                            <small>Sudah Submit</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>{{ $sudahDinilai }}</h4>
                                            <small>Sudah Dinilai</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tombol untuk melihat jawaban -->
                    @if($sudahMengerjakan > 0)
                        <div class="mt-4">
                            <a href="{{ route('dosen.penilaian.tugas', $tugas) }}" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Lihat Jawaban Mahasiswa
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 

<!-- Modal Konfirmasi fallback jika global gagal -->
<div class="modal fade" id="localConfirmModal" tabindex="-1" aria-labelledby="localConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" id="localConfirmModalHeader">
        <h5 class="modal-title" id="localConfirmModalLabel">Konfirmasi</h5>
        <!-- Tombol close dihapus -->
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

<script>
(function() {
    let localConfirmAction = null;
    function showLocalConfirmModal(message, action, type) {
        var body = document.getElementById('localConfirmModalBody');
        var header = document.getElementById('localConfirmModalHeader');
        var yesBtn = document.getElementById('localConfirmModalYes');
        if (body) body.textContent = message;
        // Set warna header dan tombol sesuai tipe aksi
        if (header && yesBtn) {
            header.className = 'modal-header';
            yesBtn.className = 'btn';
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
        if (e.target.closest('#btnToggleStatus')) {
            e.preventDefault();
            const btn = e.target.closest('#btnToggleStatus');
            const form = document.getElementById('formToggleStatus');
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