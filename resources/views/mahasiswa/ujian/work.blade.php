@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Timer Sidebar -->
        <div class="col-md-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Timer
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div id="timer" class="display-4 text-primary mb-3">
                        --:--:--
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>Durasi:</strong> {{ $jawaban->tugas->durasi_menit }} menit<br>
                            <strong>Mulai:</strong> {{ $jawaban->waktu_mulai->format('H:i:s') }}
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-secondary btn-sm" id="saveDraftBtn">
                            <i class="fas fa-save"></i> Simpan Draft
                        </button>
                    </div>
                    
                    <div id="saveStatus" class="text-muted small"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $jawaban->tugas->judul }}</h4>
                    <p class="mb-0">{{ $jawaban->tugas->mataKuliah->nama_mk }} - {{ $jawaban->tugas->dosen->name }}</p>
                </div>

                <div class="card-body">
                    <!-- Soal -->
                    <div class="alert alert-light border">
                        <h5>Soal:</h5>
                        <div class="soal-content">
                            <ol>
                                @foreach($jawaban->tugas->soal as $soal)
                                    <li class="mb-3">
                                        <div><strong>Pertanyaan:</strong> {{ $soal->pertanyaan }}</div>
                                        <div><strong>Bobot:</strong> {{ $soal->bobot }}</div>
                                        <textarea class="form-control mt-2 jawaban-soal @error('jawaban_soal.' . $soal->id) is-invalid @enderror" name="jawaban_soal[{{ $soal->id }}]" rows="4" required>{{ old('jawaban_soal.' . $soal->id, optional($jawaban->jawabanSoal->where('soal_id', $soal->id)->first())->jawaban) }}</textarea>
                                        @error('jawaban_soal.' . $soal->id)
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>

                    @if($jawaban->tugas->rubrik_penilaian)
                        <div class="alert alert-info">
                            <h6>Rubrik Penilaian:</h6>
                            <small>{!! nl2br(e($jawaban->tugas->rubrik_penilaian)) !!}</small>
                        </div>
                    @endif

                    <!-- Form Jawaban -->
                    <form id="jawabanForm" method="POST" action="{{ route('mahasiswa.ujian.submit', $jawaban) }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="jawaban">Jawaban Anda:</label>
                            <textarea class="form-control @error('jawaban') is-invalid @enderror" 
                                      id="jawaban" name="jawaban" rows="15" 
                                      placeholder="Tuliskan jawaban Anda di sini...">{{ old('jawaban', $jawaban->jawaban) }}</textarea>
                            @error('jawaban')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                Minimal 50 karakter. Saat ini: <span id="charCount">{{ strlen($jawaban->jawaban) }}</span> karakter
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input @error('confirm_submit') is-invalid @enderror" 
                                       id="confirm_submit" name="confirm_submit" required>
                                <label class="form-check-label" for="confirm_submit">
                                    Saya yakin ingin mengirim jawaban ini dan tidak dapat mengubahnya lagi.
                                </label>
                                @error('confirm_submit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Submit Jawaban
                            </button>
                            <a href="{{ route('mahasiswa.tugas.show', $jawaban->tugas) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Keluar (Draft Tersimpan)
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto Submit Modal -->
<div class="modal fade" id="autoSubmitModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Waktu Habis!</h5>
            </div>
            <div class="modal-body">
                <p>Waktu ujian telah habis. Jawaban Anda akan disubmit otomatis.</p>
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let remainingSeconds = {{ $sisaWaktu }};
    let timerElement = document.getElementById('timer');
    let saveDraftBtn = document.getElementById('saveDraftBtn');
    let saveStatus = document.getElementById('saveStatus');
    let jawabanTextarea = document.getElementById('jawaban');
    let charCount = document.getElementById('charCount');
    let submitBtn = document.getElementById('submitBtn');
    let autoSubmitModal = new bootstrap.Modal(document.getElementById('autoSubmitModal'));
    
    // Update character count
    jawabanTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
        
        // Enable/disable submit button based on character count
        if (this.value.length >= 50) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    });
    
    // Initial character count check
    if (jawabanTextarea.value.length < 50) {
        submitBtn.disabled = true;
    }
    
    // Timer countdown
    function updateTimer() {
        if (remainingSeconds <= 0) {
            // Auto submit
            autoSubmitModal.show();
            setTimeout(function() {
                // Force submit form
                document.getElementById('confirm_submit').checked = true;
                document.getElementById('jawabanForm').submit();
            }, 3000);
            return;
        }
        
        let hours = Math.floor(remainingSeconds / 3600);
        let minutes = Math.floor((remainingSeconds % 3600) / 60);
        let seconds = remainingSeconds % 60;
        
        timerElement.textContent = 
            String(hours).padStart(2, '0') + ':' + 
            String(minutes).padStart(2, '0') + ':' + 
            String(seconds).padStart(2, '0');
        
        // Change color when time is running out
        if (remainingSeconds <= 300) { // 5 minutes
            timerElement.className = 'display-4 text-danger mb-3';
        } else if (remainingSeconds <= 600) { // 10 minutes
            timerElement.className = 'display-4 text-warning mb-3';
        }
        
        remainingSeconds--;
    }
    
    // Update timer every second
    updateTimer();
    setInterval(updateTimer, 1000);
    
    // Auto save draft every 30 seconds
    setInterval(function() {
        saveDraft();
    }, 30000);
    
    // Save draft function
    function saveDraft() {
        let jawaban = jawabanTextarea.value;
        
        if (jawaban.trim() === '') {
            return;
        }
        
        fetch('{{ route("mahasiswa.ujian.save-draft", $jawaban) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                jawaban: jawaban
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                saveStatus.textContent = 'Tersimpan otomatis pada ' + data.timestamp;
                saveStatus.className = 'text-success small';
            } else {
                saveStatus.textContent = 'Gagal menyimpan';
                saveStatus.className = 'text-danger small';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            saveStatus.textContent = 'Error menyimpan';
            saveStatus.className = 'text-danger small';
        });
    }
    
    // Manual save draft
    saveDraftBtn.addEventListener('click', function() {
        saveDraft();
    });
    
    // Prevent accidental page leave
    window.addEventListener('beforeunload', function(e) {
        e.preventDefault();
        e.returnValue = '';
    });
    
    // Confirm submit
    document.getElementById('jawabanForm').addEventListener('submit', function(e) {
        if (!document.getElementById('confirm_submit').checked) {
            e.preventDefault();
            alert('Anda harus mengkonfirmasi submit jawaban.');
            return;
        }
        
        if (!confirm('Yakin ingin submit jawaban? Anda tidak dapat mengubahnya lagi setelah submit.')) {
            e.preventDefault();
        }
    });

    // Validasi minimal 50 karakter per jawaban soal
    function checkJawabanLength() {
        let valid = true;
        document.querySelectorAll('.jawaban-soal').forEach(function(textarea) {
            if (textarea.value.trim().length < 50) {
                valid = false;
            }
        });
        document.getElementById('submitBtn').disabled = !valid;
    }
    document.querySelectorAll('.jawaban-soal').forEach(function(textarea) {
        textarea.addEventListener('input', checkJawabanLength);
    });
    checkJawabanLength();
});
</script>

<style>
.sticky-top {
    z-index: 1020;
}

.soal-content {
    font-size: 1.1em;
    line-height: 1.6;
}

#jawaban {
    font-family: 'Courier New', monospace;
    font-size: 14px;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endsection

