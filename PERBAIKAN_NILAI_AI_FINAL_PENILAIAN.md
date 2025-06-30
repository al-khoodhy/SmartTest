# Perbaikan Nilai AI dan Nilai Final pada Halaman Penilaian Dosen

## Masalah
Pada halaman detail penilaian tugas dosen, kolom "Nilai AI" dan "Nilai Final" menampilkan 0.00 padahal nilai sebenarnya bukan itu. Hal ini terjadi karena sistem mengambil nilai dari tabel `Penilaian` utama, bukan dari tabel `PenilaianSoal` yang berisi nilai per soal.

## Analisis Masalah

### Sebelum Perbaikan
- **Nilai AI**: Mengambil `$j->penilaian->nilai_ai` dari tabel `Penilaian` utama
- **Nilai Manual**: Mengambil `$j->penilaian->nilai_manual` dari tabel `Penilaian` utama  
- **Nilai Final**: Mengambil `$j->nilai_akhir` yang sudah benar dari `PenilaianSoal`

### Masalah yang Ditemukan
1. **Tabel Penilaian utama**: Hanya berisi backup/arsip nilai, bukan nilai aktual
2. **Nilai aktual**: Disimpan di tabel `PenilaianSoal` (per soal)
3. **Perhitungan**: Harus menggunakan rata-rata tertimbang dari `PenilaianSoal`

## Solusi yang Diterapkan

### 1. Menambahkan Method Helper di Model (`app/Models/JawabanMahasiswa.php`)

#### Method getNilaiAiAttribute()
```php
public function getNilaiAiAttribute()
{
    $totalBobot = $this->jawabanSoal->sum(function($js) { 
        return $js->soal->bobot ?? 1;
    });
    
    if ($totalBobot == 0) return 0;
    
    $totalNilai = $this->jawabanSoal->sum(function($js) {
        $penilaian = $js->penilaian;
        if (!$penilaian || $penilaian->nilai_ai === null) return 0;
        
        $bobot = $js->soal->bobot ?? 1;
        return $penilaian->nilai_ai * $bobot;
    });
    
    $nilaiAi = round($totalNilai / $totalBobot, 2);
    
    // Pastikan nilai tidak melebihi nilai maksimal tugas
    $nilaiMaksimal = $this->tugas->nilai_maksimal ?? 100;
    return min($nilaiAi, $nilaiMaksimal);
}
```

#### Method getNilaiManualAttribute()
```php
public function getNilaiManualAttribute()
{
    $totalBobot = $this->jawabanSoal->sum(function($js) { 
        return $js->soal->bobot ?? 1;
    });
    
    if ($totalBobot == 0) return 0;
    
    $totalNilai = $this->jawabanSoal->sum(function($js) {
        $penilaian = $js->penilaian;
        if (!$penilaian || $penilaian->nilai_manual === null) return 0;
        
        $bobot = $js->soal->bobot ?? 1;
        return $penilaian->nilai_manual * $bobot;
    });
    
    $nilaiManual = round($totalNilai / $totalBobot, 2);
    
    // Pastikan nilai tidak melebihi nilai maksimal tugas
    $nilaiMaksimal = $this->tugas->nilai_maksimal ?? 100;
    return min($nilaiManual, $nilaiMaksimal);
}
```

### 2. Memperbarui Controller (`app/Http/Controllers/Dosen/PenilaianController.php`)

#### Sebelum
```php
$jawaban = $tugas->jawabanMahasiswa()
    ->with(['mahasiswa', 'penilaian'])
    ->where('status', '!=', 'draft')
    ->latest()
    ->paginate(15);
```

#### Sesudah
```php
$jawaban = $tugas->jawabanMahasiswa()
    ->with(['mahasiswa', 'penilaian', 'jawabanSoal.soal', 'jawabanSoal.penilaian'])
    ->where('status', '!=', 'draft')
    ->latest()
    ->paginate(15);
```

### 3. Memperbarui View (`resources/views/dosen/penilaian/tugas.blade.php`)

#### Kolom Nilai AI
```php
@if($tugas->auto_grade && $j->nilai_ai > 0)
    <span class="badge bg-info text-dark">{{ $j->nilai_ai }}</span>
@else
    <span class="text-muted">-</span>
@endif
```

#### Kolom Nilai Manual
```php
@if($j->nilai_manual > 0)
    <span class="badge bg-success text-light">{{ $j->nilai_manual }}</span>
@else
    <span class="text-muted">-</span>
@endif
```

#### Kolom Nilai Final
```php
@if($j->status == 'graded' && $j->nilai_akhir > 0)
    <span class="badge bg-primary text-light fs-6">{{ $j->nilai_akhir }}</span>
@elseif($tugas->auto_grade && $j->nilai_ai > 0)
    <span class="badge bg-info text-dark">{{ $j->nilai_ai }}</span>
    <br><small class="text-muted">Nilai AI</small>
@else
    <span class="badge bg-warning text-dark">Menunggu Penilaian</span>
@endif
```

## Hasil Perbaikan

### Sebelum Perbaikan
- **Nilai AI**: 60.00 (dari tabel Penilaian utama)
- **Nilai Manual**: 78.00 (dari tabel Penilaian utama)
- **Nilai Final**: 73.75 (sudah benar dari PenilaianSoal)

### Sesudah Perbaikan
- **Nilai AI**: 72.00 (dihitung dari PenilaianSoal dengan bobot)
- **Nilai Manual**: 91.75 (dihitung dari PenilaianSoal dengan bobot)
- **Nilai Final**: 73.75 (tetap benar)

## Detail Perhitungan

### Nilai AI (72.00)
```
Soal 1: Bobot 1, Nilai AI 61.00 → Kontribusi: 61.00
Soal 2: Bobot 1, Nilai AI 67.00 → Kontribusi: 67.00  
Soal 3: Bobot 2, Nilai AI 80.00 → Kontribusi: 160.00
Total bobot: 4
Total nilai: 288.00
Nilai rata-rata: 288/4 = 72.00
```

### Nilai Manual (91.75)
```
Soal 1: Bobot 1, Nilai Manual 98.00 → Kontribusi: 98.00
Soal 2: Bobot 1, Nilai Manual 71.00 → Kontribusi: 71.00  
Soal 3: Bobot 2, Nilai Manual 99.00 → Kontribusi: 198.00
Total bobot: 4
Total nilai: 367.00
Nilai rata-rata: 367/4 = 91.75
```

## Keuntungan Perbaikan

1. **Akurasi**: Nilai yang ditampilkan sesuai dengan data aktual di `PenilaianSoal`
2. **Konsistensi**: Semua nilai menggunakan sumber data yang sama
3. **Bobot**: Perhitungan memperhitungkan bobot soal
4. **Capping**: Nilai tidak melebihi nilai maksimal tugas
5. **Transparansi**: Dosen dapat melihat nilai yang sebenarnya

## File yang Diubah

1. `app/Models/JawabanMahasiswa.php`
   - Menambahkan method `getNilaiAiAttribute()`
   - Menambahkan method `getNilaiManualAttribute()`

2. `app/Http/Controllers/Dosen/PenilaianController.php`
   - Menambahkan eager loading `jawabanSoal.soal` dan `jawabanSoal.penilaian`

3. `resources/views/dosen/penilaian/tugas.blade.php`
   - Menggunakan method baru `$j->nilai_ai` dan `$j->nilai_manual`
   - Memperbaiki logika tampilan nilai

## Test Verifikasi

Script test: `test_fix_nilai_penilaian.php`
- ✅ Memverifikasi perhitungan nilai AI yang benar
- ✅ Memverifikasi perhitungan nilai manual yang benar
- ✅ Membandingkan nilai lama vs baru
- ✅ Memastikan tidak ada lagi nilai 0.00 yang salah

## Kesimpulan

Perbaikan ini memastikan bahwa:
- Nilai AI menampilkan nilai yang benar dari `PenilaianSoal`
- Nilai Manual menampilkan nilai yang benar dari `PenilaianSoal`
- Nilai Final tetap menampilkan nilai yang benar
- Tidak ada lagi nilai 0.00 yang menyesatkan
- Perhitungan memperhitungkan bobot soal dengan benar 