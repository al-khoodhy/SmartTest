@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Timer Sidebar -->
        <div class="col-lg-3 col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Timer Ujian
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div id="timer" class="display-4 text-primary mb-3 fw-bold">
                        <span id="timer-hours">00</span>:<span id="timer-minutes">00</span>:<span id="timer-seconds">00</span>
                    </div>
                    
                    <div class="alert alert-info small">
                        <div class="row text-start">
                            <div class="col-6"><strong>Durasi:</strong></div>
                            <div class="col-6">{{ $jawaban->tugas->durasi_menit }} menit</div>
                            <div class="col-6"><strong>Mulai:</strong></div>
                            <div class="col-6">{{ $jawaban->waktu_mulai->format('H:i:s') }}</div>
                            <div class="col-6"><strong>Deadline:</strong></div>
                            <div class="col-6">{{ $jawaban->tugas->deadline->format('H:i:s') }}</div>
                            <div class="col-6"><strong>Sisa Waktu:</strong></div>
                            <div class="col-6" id="debug-time">{{ $sisaWaktu }} detik</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="saveDraftBtn">
                            <i class="fas fa-save"></i> Simpan Draft
                        </button>
                    </div>
                    
                    <div id="saveStatus" class="text-muted small"></div>
                    
                    <!-- Progress indicator -->
                    <div class="mt-3">
                        <div class="progress" style="height: 8px;">
                            <div id="timeProgress" class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Sisa Waktu</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">{{ $jawaban->tugas->judul }}</h4>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-book"></i> {{ $jawaban->tugas->mataKuliah->nama_mk }} | 
                                <i class="fas fa-user-tie"></i> {{ $jawaban->tugas->dosen->name }}
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">{{ $jawaban->tugas->soal->count() }} Soal</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Rubrik Penilaian -->
                    @if($jawaban->tugas->rubrik_penilaian)
                        <div class="alert alert-info border-start border-4 border-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle"></i> Rubrik Penilaian
                            </h6>
                            <div class="small">{!! nl2br(e($jawaban->tugas->rubrik_penilaian)) !!}</div>
                        </div>
                    @endif

                    <!-- Form Jawaban -->
                    <form id="jawabanForm" method="POST" action="{{ route('mahasiswa.ujian.submit', $jawaban) }}">
                        @csrf
                        
                        <!-- Soal dan Jawaban -->
                        <div class="soal-container">
                            @foreach($jawaban->tugas->soal as $index => $soal)
                                <div class="card mb-4 border">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                                Soal {{ $index + 1 }}
                                            </h6>
                                            <span class="badge bg-info">Bobot: {{ $soal->bobot }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6 class="fw-bold">Pertanyaan:</h6>
                                            <div class="p-3 bg-light rounded">
                                                {!! nl2br(e($soal->pertanyaan)) !!}
                                            </div>
                                        </div>
                        
                        <div class="form-group">
                                            <label for="jawaban_soal_{{ $soal->id }}" class="form-label fw-bold">
                                                Jawaban Anda:
                                            </label>
                                            <textarea 
                                                class="form-control jawaban-soal @error('jawaban_soal.' . $soal->id) is-invalid @enderror" 
                                                id="jawaban_soal_{{ $soal->id }}"
                                                name="jawaban_soal[{{ $soal->id }}]" 
                                                rows="6" 
                                                placeholder="Tuliskan jawaban Anda di sini..."
                                                required
                                            >{{ old('jawaban_soal.' . $soal->id, optional($jawaban->jawabanSoal->where('soal_id', $soal->id)->first())->jawaban) }}</textarea>
                                            
                                            @error('jawaban_soal.' . $soal->id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                                            
                                            <div class="form-text">
                                                <span class="char-count" data-target="jawaban_soal_{{ $soal->id }}">
                                                    {{ strlen(optional($jawaban->jawabanSoal->where('soal_id', $soal->id)->first())->jawaban) }}
                                                </span> 
                                                karakter
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Konfirmasi Submit -->
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-exclamation-triangle"></i> Konfirmasi Submit
                                </h6>
                            </div>
                            <div class="card-body">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input @error('confirm_submit') is-invalid @enderror" 
                                       id="confirm_submit" name="confirm_submit" required>
                                <label class="form-check-label" for="confirm_submit">
                                        <strong>Saya yakin ingin mengirim jawaban ini dan tidak dapat mengubahnya lagi.</strong>
                                </label>
                                @error('confirm_submit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-secondary btn-lg me-2" id="submitBtn" disabled data-bs-toggle="modal" data-bs-target="#modalSubmitJawaban">
                                <i class="fas fa-paper-plane"></i> Submit Jawaban
                            </button>
                            <a href="{{ route('mahasiswa.tugas.show', $jawaban->tugas) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Keluar (Draft Tersimpan)
                            </a>
                                </div>
                                
                                <!-- Debug info -->
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Status: <span id="debug-status">Menunggu input...</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto Submit Modal -->
<div class="modal fade" id="autoSubmitModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Waktu Habis!
                </h5>
            </div>
            <div class="modal-body text-center">
                <p class="mb-3">Waktu ujian telah habis. Jawaban Anda akan disubmit otomatis.</p>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted small">Mohon tunggu...</p>
            </div>
        </div>
    </div>
</div>

<!-- Warning Modal for Page Leave -->
<div class="modal fade" id="pageLeaveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Peringatan
                </h5>
            </div>
            <div class="modal-body">
                <p>Anda akan meninggalkan halaman ujian. Pastikan Anda telah menyimpan draft jawaban Anda.</p>
                <p class="text-muted small">Draft akan otomatis tersimpan setiap 30 detik.</p>
                    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tinggal di Halaman Ini</button>
                <button type="button" class="btn btn-danger" id="confirmLeave">Ya, Tinggalkan Halaman</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Submit Jawaban -->
<div class="modal fade" id="modalSubmitJawaban" tabindex="-1" aria-labelledby="modalSubmitJawabanLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalSubmitJawabanLabel">Konfirmasi Submit Jawaban</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin mengirim jawaban ini? Setelah submit, Anda <b>tidak dapat mengubah jawaban</b>.<br>
        Pastikan semua jawaban sudah benar dan lengkap.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnKonfirmasiSubmitJawaban">Ya, Kirim Jawaban</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let remainingSeconds = {{ $sisaWaktu }};
    let totalSeconds = {{ $sisaWaktu }};
    let timerHours = document.getElementById('timer-hours');
    let timerMinutes = document.getElementById('timer-minutes');
    let timerSeconds = document.getElementById('timer-seconds');
    let timeProgress = document.getElementById('timeProgress');
    let debugTimeElement = document.getElementById('debug-time');
    let debugStatusElement = document.getElementById('debug-status');
    let saveDraftBtn = document.getElementById('saveDraftBtn');
    let saveStatus = document.getElementById('saveStatus');
    let submitBtn = document.getElementById('submitBtn');
    let autoSubmitModal = new bootstrap.Modal(document.getElementById('autoSubmitModal'));
    let pageLeaveModal = new bootstrap.Modal(document.getElementById('pageLeaveModal'));
    let hasUnsavedChanges = false;
    let timerInterval;
    
    console.log('Initial timer values:', { remainingSeconds, totalSeconds });
    console.log('Submit button found:', submitBtn);
    
    // Character count validation for all textareas
    document.querySelectorAll('.jawaban-soal').forEach(function(textarea, index) {
        const charCountElement = textarea.parentElement.querySelector('.char-count');
        
        console.log(`Initializing textarea ${index + 1}:`, textarea);
        
        // Real-time character counting
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            charCountElement.textContent = length;
            
            // Remove any validation styling since there's no minimum requirement
            this.classList.remove('is-invalid');
            charCountElement.classList.remove('text-danger', 'text-success');
            
            hasUnsavedChanges = true;
            validateAllAnswers();
        });
        
        // Initial character count display
        const length = textarea.value.length;
        charCountElement.textContent = length;
    });
    
    // Validate all answers
    function validateAllAnswers() {
        const textareas = document.querySelectorAll('.jawaban-soal');
        let allValid = true;
        textareas.forEach(function(textarea) {
            const charCountElement = textarea.parentElement.querySelector('.char-count');
            const length = textarea.value.length;
            charCountElement.textContent = length;
            
            // Only check if answer is not empty (required field)
            if (length > 0) {
                textarea.classList.remove('is-invalid');
                charCountElement.classList.remove('text-danger');
                charCountElement.classList.add('text-success');
            } else {
                textarea.classList.add('is-invalid');
                charCountElement.classList.remove('text-success');
                charCountElement.classList.add('text-danger');
                allValid = false;
            }
        });
        const confirmChecked = document.getElementById('confirm_submit').checked;
        submitBtn.disabled = !(allValid && confirmChecked);
        if (submitBtn.disabled) {
            submitBtn.classList.add('btn-secondary');
            submitBtn.classList.remove('btn-success');
        } else {
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-success');
        }
        // Debug status
        if (debugStatusElement) {
            if (allValid && confirmChecked) {
                debugStatusElement.textContent = '✅ Siap untuk submit';
                debugStatusElement.className = 'text-success';
            } else if (!allValid && !confirmChecked) {
                debugStatusElement.textContent = '❌ Jawaban belum valid & konfirmasi belum dicentang';
                debugStatusElement.className = 'text-danger';
            } else if (!allValid) {
                debugStatusElement.textContent = '❌ Jawaban belum valid (harus diisi)';
                debugStatusElement.className = 'text-danger';
            } else {
                debugStatusElement.textContent = '❌ Konfirmasi belum dicentang';
                debugStatusElement.className = 'text-danger';
            }
        }
    }
    
    // Attach event listener ke semua textarea dan checkbox
    document.querySelectorAll('.jawaban-soal').forEach(function(textarea) {
        textarea.addEventListener('input', validateAllAnswers);
        textarea.addEventListener('change', validateAllAnswers);
    });
    document.getElementById('confirm_submit').addEventListener('change', validateAllAnswers);
    
    // Timer countdown with server sync
    function updateTimer() {
        if (remainingSeconds <= 0) {
            // Auto submit
            clearInterval(timerInterval);
            console.log('Time is up! Auto submitting...');
            autoSubmitModal.show();
            setTimeout(function() {
                document.getElementById('confirm_submit').checked = true;
                document.getElementById('jawabanForm').submit();
            }, 3000);
            return;
        }
        
        let hours = Math.floor(remainingSeconds / 3600);
        let minutes = Math.floor((remainingSeconds % 3600) / 60);
        let seconds = remainingSeconds % 60;
        
        // Update individual timer elements
        timerHours.textContent = String(hours).padStart(2, '0');
        timerMinutes.textContent = String(minutes).padStart(2, '0');
        timerSeconds.textContent = String(seconds).padStart(2, '0');
        
        // Update debug element
        if (debugTimeElement) {
            debugTimeElement.textContent = remainingSeconds + ' detik';
        }
        
        // Update progress bar
        const progressPercent = (remainingSeconds / totalSeconds) * 100;
        timeProgress.style.width = progressPercent + '%';
        
        // Change color when time is running out
        if (remainingSeconds <= 300) { // 5 minutes
            timerHours.parentElement.className = 'display-4 text-danger mb-3 fw-bold';
            timeProgress.className = 'progress-bar bg-danger';
        } else if (remainingSeconds <= 600) { // 10 minutes
            timerHours.parentElement.className = 'display-4 text-warning mb-3 fw-bold';
            timeProgress.className = 'progress-bar bg-warning';
        } else {
            timerHours.parentElement.className = 'display-4 text-primary mb-3 fw-bold';
            timeProgress.className = 'progress-bar bg-success';
        }
        
        remainingSeconds--;
        
        console.log('Timer update:', { 
            hours: timerHours.textContent, 
            minutes: timerMinutes.textContent, 
            seconds: timerSeconds.textContent, 
            remainingSeconds, 
            progressPercent 
        });
    }
    
    // Start timer
    function startTimer() {
        // Clear any existing interval
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        // Initial update
    updateTimer();
        
        // Set interval for countdown
        timerInterval = setInterval(updateTimer, 1000);
        
        console.log('Timer started with interval:', timerInterval);
    }
    
    // Start the timer
    startTimer();
    
    // Sync with server every 30 seconds
    setInterval(function() {
        console.log('Syncing with server...');
        fetch('{{ route("mahasiswa.ujian.get-remaining-time", $jawaban) }}')
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                if (data.remaining_seconds !== undefined) {
                    remainingSeconds = data.remaining_seconds;
                    totalSeconds = Math.max(totalSeconds, remainingSeconds);
                    
                    // Restart timer with new values
                    startTimer();
                }
            })
            .catch(error => console.error('Error syncing timer:', error));
    }, 30000);
    
    // Auto save draft every 30 seconds
    setInterval(function() {
        if (hasUnsavedChanges) {
        saveDraft();
        }
    }, 30000);
    
    // Save draft function
    function saveDraft() {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        
        // Collect all jawaban soal
        const jawabanSoal = {};
        document.querySelectorAll('.jawaban-soal').forEach(function(textarea) {
            const soalId = textarea.name.match(/\[(\d+)\]/)[1];
            jawabanSoal[soalId] = textarea.value;
        });
        
        formData.append('jawaban_soal', JSON.stringify(jawabanSoal));
        
        fetch('{{ route("mahasiswa.ujian.save-draft", $jawaban) }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                saveStatus.textContent = 'Tersimpan otomatis pada ' + data.timestamp;
                saveStatus.className = 'text-success small';
                hasUnsavedChanges = false;
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
        if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
        }
    });
    
    // Handle page leave with modal
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' && e.target.href && !e.target.href.includes('javascript:') && hasUnsavedChanges) {
            e.preventDefault();
            document.getElementById('confirmLeave').onclick = function() {
                window.location.href = e.target.href;
            };
            pageLeaveModal.show();
        }
    });

    // Initial validation
    console.log('Running initial validation...');
    validateAllAnswers();
    
    // Debug timer
    console.log('Timer initialized with:', {
        remainingSeconds: remainingSeconds,
        totalSeconds: totalSeconds,
        timerHours: timerHours,
        timerMinutes: timerMinutes,
        timerSeconds: timerSeconds,
        timeProgress: timeProgress,
        submitBtn: submitBtn
    });

    var btnKonfirmasi = document.getElementById('btnKonfirmasiSubmitJawaban');
    if(btnKonfirmasi) {
        btnKonfirmasi.addEventListener('click', function() {
            // Pastikan tidak ada confirm() atau event JS lain
            document.getElementById('jawabanForm').onsubmit = null;
            document.getElementById('jawabanForm').submit();
        });
    }
});
</script>

<style>
.sticky-top {
    z-index: 1020;
}

.jawaban-soal {
    resize: vertical;
    min-height: 120px;
}

.char-count {
    font-weight: bold;
}

.progress {
    background-color: #e9ecef;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.soal-container .card {
    transition: all 0.3s ease;
}

.soal-container .card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

@media (max-width: 768px) {
    .sticky-top {
        position: relative !important;
        top: 0 !important;
    }
    
    .display-4 {
        font-size: 2rem;
    }
}
</style>
@endsection

