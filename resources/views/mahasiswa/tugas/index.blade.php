@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 768px) {
        .table-responsive table {
            font-size: 0.8em;
        }
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Kumpulan Tugas</h4>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form class="mb-4" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="kelas_id">Mata Kuliah/Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-control">
                                    <option value="">Semua</option>
                                    @foreach($mataKuliah as $mk)
                                        <option value="{{ $mk->id }}" {{ request('kelas_id') == $mk->id ? 'selected' : '' }}>
                                            {{ $mk->nama_mk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Semua</option>
                                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Sudah Submit</option>
                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('mahasiswa.tugas.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <!-- Statistik -->
                    @if($tugas->count() > 0)
                    <div class="row mb-4">
                        @php
                            $availableCount = 0;
                            $inProgressCount = 0;
                            $submittedCount = 0;
                            $expiredCount = 0;
                            
                            foreach($tugas as $t) {
                                $jawaban = $jawabanStatus[$t->id] ?? null;
                                $now = \Carbon\Carbon::now();
                                $isExpired = $t->deadline <= $now;
                                
                                if($jawaban) {
                                    if($jawaban->status === 'draft') $inProgressCount++;
                                    else $submittedCount++;
                                } elseif($isExpired) {
                                    $expiredCount++;
                                } else {
                                    $availableCount++;
                                }
                            }
                        @endphp
                        
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $availableCount }}</h5>
                                    <small>Tersedia</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $inProgressCount }}</h5>
                                    <small>Sedang Dikerjakan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $submittedCount }}</h5>
                                    <small>Sudah Submit</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>{{ $expiredCount }}</h5>
                                    <small>Expired</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($tugas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>Judul Tugas</th>
                                        <th>Dosen</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th>Nilai</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tugas as $t)
                                        @php
                                            $jawaban = $jawabanStatus[$t->id] ?? null;
                                            $now = \Carbon\Carbon::now();
                                            $isExpired = $t->deadline <= $now;
                                            $canWork = !$jawaban && !$isExpired && $t->is_active;
                                            $canContinue = $jawaban && $jawaban->status === 'draft' && !$isExpired;
                                            $timeLeft = $t->deadline->diffForHumans();
                                            $sudahDinilai = false;
                                            if($jawaban && $jawaban->penilaian && in_array($jawaban->penilaian->status_penilaian, ['ai_graded', 'final'])) {
                                                $sudahDinilai = true;
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $t->mataKuliah->nama_mk }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $t->kelas->nama_kelas ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $t->judul }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Str::limit($t->deskripsi, 50) }}</small>
                                                @if($t->auto_grade)
                                                    <br><small class="text-info"><i class="bi bi-robot"></i> Auto Grade</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $t->dosen ? $t->dosen->name : 'Dosen tidak ditemukan' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $t->deadline->format('d/m/Y H:i') }}</small>
                                                @if(!$isExpired && $t->deadline->diffInHours($now) <= 24)
                                                    <br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> {{ $timeLeft }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                $statusBadge = '';
                                                if($jawaban) {
                                                    if($jawaban->status === 'draft') {
                                                        $statusBadge = '<span class="badge bg-warning text-dark">Sedang Dikerjakan</span>';
                                                    } elseif($jawaban->penilaian && $jawaban->penilaian->status_penilaian == 'ai_graded') {
                                                        $statusBadge = '<span class="badge bg-info text-dark">AI Graded</span>';
                                                    } elseif(($jawaban->penilaian && $jawaban->penilaian->status_penilaian == 'final') || $jawaban->status === 'graded') {
                                                        $statusBadge = '<span class="badge bg-success">Sudah Dinilai</span>';
                                                    } elseif($jawaban->status === 'submitted') {
                                                        $statusBadge = '<span class="badge bg-info text-dark">Menunggu Penilaian</span>';
                                                    }
                                                } elseif($isExpired) {
                                                    $statusBadge = '<span class="badge bg-danger">Expired</span>';
                                                } else {
                                                    $statusBadge = '<span class="badge bg-primary">Tersedia</span>';
                                                }
                                                @endphp
                                                {!! $statusBadge !!}
                                            </td>
                                            <td>
                                                @if($jawaban && $jawaban->penilaian)
                                                    <span class="badge bg-success fs-6">{{ $jawaban->nilai_akhir }}</span>
                                                    @if($jawaban->penilaian->status_penilaian == 'ai_graded')
                                                        <br><small class="text-muted"><i class="bi bi-robot"></i> AI</small>
                                                    @elseif($jawaban->penilaian->status_penilaian == 'final')
                                                        <br><small class="text-muted"><i class="bi bi-person-check"></i> Manual</small>
                                                    @endif
                                                @elseif($t->auto_grade && $jawaban && $jawaban->nilai_ai > 0)
                                                    <span class="badge bg-info text-dark fs-6">{{ $jawaban->nilai_ai }}</span>
                                                    <br><small class="text-muted"><i class="bi bi-robot"></i> AI</small>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('mahasiswa.tugas.show', $t) }}" class="btn btn-sm btn-outline-primary" title="Detail Tugas">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    
                                                    @if($canWork)
                                                        <form id="mulaiKerjaForm-{{ $t->id }}" action="{{ route('mahasiswa.tugas.start', $t) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalMulaiKerja-{{ $t->id }}" title="Mulai Kerjakan">
                                                                <i class="bi bi-play-circle"></i>
                                                            </button>
                                                        </form>
                                                        <!-- Modal Konfirmasi Bootstrap -->
                                                        <div class="modal fade" id="modalMulaiKerja-{{ $t->id }}" tabindex="-1" aria-labelledby="modalMulaiKerjaLabel-{{ $t->id }}" aria-hidden="true">
                                                          <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                              <div class="modal-header">
                                                                <h5 class="modal-title" id="modalMulaiKerjaLabel-{{ $t->id }}">Konfirmasi Mulai Tugas</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                              </div>
                                                              <div class="modal-body">
                                                                Apakah Anda yakin ingin mulai mengerjakan tugas ini? Setelah dimulai, waktu akan berjalan dan Anda harus menyelesaikan dalam satu kali kesempatan.
                                                              </div>
                                                              <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="button" class="btn btn-primary" onclick="document.getElementById('mulaiKerjaForm-{{ $t->id }}').submit();">Ya, Mulai</button>
                                                              </div>
                                                            </div>
                                                          </div>
                                                        </div>
                                                    @elseif($canContinue)
                                                        <a href="{{ route('mahasiswa.ujian.work', $jawaban) }}" class="btn btn-sm btn-warning" title="Lanjutkan">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                    @elseif($jawaban && in_array($jawaban->status, ['submitted', 'graded']))
                                                        <a href="{{ route('mahasiswa.nilai.show', $jawaban) }}" class="btn btn-sm btn-info" title="Lihat Nilai">
                                                            <i class="bi bi-graph-up"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $tugas->appends(request()->query())->links('vendor.pagination.simple-numeric') }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-journal-x fa-3x text-muted mb-3"></i>
                            <h5>Tidak ada tugas</h5>
                            <p class="text-muted">
                                @if(request()->has('kelas_id') || request()->has('status'))
                                    Tidak ada tugas yang sesuai dengan filter yang dipilih.
                                @else
                                    Belum ada tugas yang tersedia untuk Anda saat ini.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-submit filter form when select changes
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#kelas_id, #status');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});
</script>
@endsection

