# Perbaikan Error whereHas pada Halaman Penilaian Dosen

## Masalah
Terjadi error pada halaman penilaian dosen:
```
Method Illuminate\Database\Eloquent\Collection::whereHas does not exist.
Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

## Analisis Masalah
Error terjadi karena penggunaan method `whereHas()` pada Collection di file `resources/views/dosen/penilaian/index.blade.php`. Method `whereHas()` hanya tersedia pada Query Builder, bukan pada Collection.

### Kode Bermasalah
```php
$aiGradedJawaban = $t->jawabanMahasiswa->whereHas('penilaian', function($q) {
    $q->where('status_penilaian', 'ai_graded');
})->count();
```

## Solusi yang Diterapkan

### 1. Perbaikan View (`resources/views/dosen/penilaian/index.blade.php`)

#### Sebelum (Bermasalah)
```php
$aiGradedJawaban = $t->jawabanMahasiswa->whereHas('penilaian', function($q) {
    $q->where('status_penilaian', 'ai_graded');
})->count();
```

#### Sesudah (Diperbaiki)
```php
$aiGradedJawaban = $t->jawabanMahasiswa->filter(function($jawaban) {
    return $jawaban->penilaian && $jawaban->penilaian->status_penilaian === 'ai_graded';
})->count();
```

### 2. Perbaikan Controller (`app/Http/Controllers/Dosen/PenilaianController.php`)

#### Sebelum
```php
$tugasQuery = Tugas::where('dosen_id', $dosen->id)->with('kelas.mataKuliah');
```

#### Sesudah
```php
$tugasQuery = Tugas::where('dosen_id', $dosen->id)
    ->with(['kelas.mataKuliah', 'jawabanMahasiswa.penilaian']);
```

## Penjelasan Perbaikan

### 1. Penggunaan Method yang Tepat
- **whereHas()**: Hanya tersedia pada Query Builder
- **filter()**: Tersedia pada Collection dan berfungsi untuk filtering data
- **where()**: Tersedia pada Collection untuk filtering sederhana

### 2. Eager Loading yang Benar
- Menambahkan `jawabanMahasiswa.penilaian` ke eager loading
- Memastikan relasi `penilaian` dimuat sebelum digunakan di view
- Meningkatkan performa query dengan mengurangi N+1 problem

### 3. Logika Filtering yang Konsisten
```php
// Filter jawaban yang memiliki penilaian dengan status 'ai_graded'
$aiGradedJawaban = $t->jawabanMahasiswa->filter(function($jawaban) {
    return $jawaban->penilaian && $jawaban->penilaian->status_penilaian === 'ai_graded';
})->count();
```

## Perbedaan Method Collection vs Query Builder

| Method | Collection | Query Builder | Keterangan |
|--------|------------|---------------|------------|
| `where()` | ✅ Tersedia | ✅ Tersedia | Filter sederhana |
| `filter()` | ✅ Tersedia | ❌ Tidak ada | Filter dengan closure |
| `whereHas()` | ❌ Tidak ada | ✅ Tersedia | Filter relasi |
| `count()` | ✅ Tersedia | ✅ Tersedia | Hitung jumlah |

## Hasil Perbaikan

### Sebelum Perbaikan
- ❌ Error 500: "Method whereHas does not exist"
- ❌ Halaman penilaian tidak dapat diakses
- ❌ Relasi tidak dimuat dengan benar

### Sesudah Perbaikan
- ✅ Halaman penilaian dapat diakses tanpa error
- ✅ Status penilaian ditampilkan dengan benar
- ✅ Performa query lebih baik dengan eager loading
- ✅ Logika filtering berfungsi dengan benar

## Test Verifikasi

Script test: `test_fix_penilaian_error.php`
- ✅ Memverifikasi relasi dimuat dengan benar
- ✅ Memverifikasi logika filtering berfungsi
- ✅ Memverifikasi status penilaian ditampilkan dengan benar
- ✅ Memverifikasi tidak ada error whereHas

## File yang Diubah

1. `resources/views/dosen/penilaian/index.blade.php`
   - Mengganti `whereHas()` dengan `filter()`
   - Memperbaiki logika perhitungan AI graded jawaban

2. `app/Http/Controllers/Dosen/PenilaianController.php`
   - Menambahkan eager loading `jawabanMahasiswa.penilaian`
   - Memastikan relasi dimuat sebelum digunakan di view

## Kesimpulan

Perbaikan ini memastikan bahwa:
- Error whereHas pada Collection sudah diperbaiki
- Halaman penilaian dosen dapat diakses tanpa error
- Status penilaian ditampilkan dengan benar dan informatif
- Performa query lebih baik dengan eager loading yang tepat
- Logika filtering menggunakan method yang sesuai dengan tipe data

## Best Practices

1. **Gunakan method yang tepat**: `whereHas()` untuk Query Builder, `filter()` untuk Collection
2. **Eager loading**: Muat relasi yang diperlukan sebelum digunakan di view
3. **Test relasi**: Pastikan relasi dimuat dengan `relationLoaded()`
4. **Error handling**: Tangani kasus di mana relasi mungkin null 