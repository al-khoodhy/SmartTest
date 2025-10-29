# SmartTest

SmartTest adalah platform manajemen tugas, penilaian, dan pembelajaran berbasis web untuk perguruan tinggi, dengan fitur penilaian otomatis (AI Grading) menggunakan Gemini AI. Sistem ini mendukung peran Admin, Dosen, dan Mahasiswa, serta mengadopsi permission system Voyager untuk kontrol akses yang fleksibel.

---

## Daftar Isi
- [Fitur Utama](#fitur-utama)
- [Arsitektur Peran & Permission](#arsitektur-peran--permission)
- [Instalasi & Setup](#instalasi--setup)
- [Konfigurasi AI Gemini](#konfigurasi-ai-gemini)
- [Alur Kerja & User Flow](#alur-kerja--user-flow)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)

---

## Fitur Utama
- **Penilaian Otomatis (AI Grading):**
  - Tugas esai mahasiswa dapat dinilai otomatis oleh AI (Gemini) dengan feedback real-time.
  - Feedback AI dan manual dosen tampil terpisah dan jelas.
- **Manajemen Tugas & Ujian:**
  - Dosen dapat membuat, mengelola, dan menilai tugas/ujian.
  - Mahasiswa dapat mengerjakan, submit, dan melihat hasil tugas/ujian.
- **Dashboard Peran:**
  - Statistik, progress, dan quick action untuk dosen & mahasiswa.
- **Permission Granular (Voyager):**
  - Setiap fitur dikontrol permission, dapat diatur via admin panel.
- **Manajemen Kelas & Mata Kuliah:**
  - Relasi dosen-kelas, mahasiswa-kelas, dan mata kuliah.
- **Import Mahasiswa via CSV:**
  - Admin dapat menambah mahasiswa secara massal.
- **Progress Penilaian & Feedback Detail:**
  - Progress bar, detail nilai per soal, feedback AI/manual, dan kategori nilai.
- **Export Nilai:**
  - Dosen dapat mengunduh nilai tugas dalam format Excel.

---

## Arsitektur Peran & Permission

### Peran Utama
- **Admin:**
  - Kelola user, dosen, mahasiswa, permission, dan data master via Voyager admin panel.
- **Dosen:**
  - Buat & kelola tugas, kelas, mata kuliah, penilaian manual/AI, ekspor nilai.
- **Mahasiswa:**
  - Mengerjakan tugas/ujian, melihat nilai & feedback, progress belajar.

### Permission (Voyager)
- Permission diatur via Voyager (`/admin` > Tools > Roles).
- Contoh permission dosen: `manage_tugas`, `grade_tugas`, `export_nilai`.
- Contoh permission mahasiswa: `view_tugas`, `submit_tugas`, `take_ujian`.
- Permission dapat diubah tanpa coding.

---

## Instalasi & Setup

### 1. Prasyarat
- PHP >= 8.1
- Composer
- Node.js & npm
- MySQL/MariaDB

### 2. Clone & Install
```bash
# Clone repo
$ git clone <repo-url> smarttest
$ cd smarttest

# Install dependency backend
$ composer install

# Install dependency frontend
$ npm install
```

### 3. Konfigurasi Environment
- Copy file `.env.example` (jika ada) ke `.env` dan sesuaikan DB, mail, Gemini API, dsb.
- Generate app key:
```bash
php artisan key:generate
```

### 4. Migrasi & Seeder Database
```bash
php artisan migrate:fresh --seed
```
Seeder akan membuat data roles, permission, admin default, contoh dosen/mahasiswa, dsb.

### 5. Build Frontend
```bash
npm run build
```

### 6. Jalankan Server
```bash
php artisan serve
```
Akses di `http://localhost:8000`.

### 7. Setup Voyager (Opsional)
```bash
php artisan voyager:setup-permissions
php artisan voyager:setup-bread
```

---

## Konfigurasi AI Gemini
- Diperlukan API Key Gemini (Google AI):
  - Tambahkan di `.env`:
    - `GEMINI_API_KEY=...`
    - `GEMINI_API_URL=...` (jika custom endpoint)
- Test koneksi:
```bash
php artisan gemini:test
```

---

## Alur Kerja & User Flow
1. **Admin** setup roles, permission, user, kelas, mata kuliah via Voyager.
2. **Dosen** membuat tugas, soal, dan mengatur auto-grading jika diinginkan.
3. **Mahasiswa** mengerjakan tugas, submit jawaban.
4. **Sistem** melakukan auto-grading (jika aktif) dan simpan feedback AI.
5. **Dosen** dapat menilai manual, override nilai, dan memberi feedback tambahan.
6. **Mahasiswa** melihat nilai akhir, feedback AI/manual, dan progress penilaian.

---

## Panduan Penggunaan

### Admin
- Login ke `/admin` (default: admin@admin.com / password)
- Kelola user, roles, permission, kelas, mata kuliah, dsb.
- Import mahasiswa via CSV.

### Dosen
- Login, akses dashboard dosen.
- Buat tugas, soal, atur auto-grading.
- Lihat & nilai jawaban mahasiswa (manual/AI).
- Ekspor nilai ke Excel.

### Mahasiswa
- Login, akses dashboard mahasiswa.
- Lihat daftar tugas/ujian.
- Kerjakan & submit tugas.
- Lihat nilai akhir, feedback AI/manual, dan detail penilaian per soal.

---

## Testing

### Unit & Feature Test
- Framework: PHPUnit
- Lokasi: `tests/`
- Jalankan:
```bash
php artisan test
# atau
vendor/bin/phpunit
```

### Contoh Test
- Autentikasi, role protection, penilaian AI/manual, relasi dosen-kelas, dsb.
- Cek file di `tests/Feature/` dan `tests/Unit/`.

---

## Troubleshooting
- **Permission error:**
  - Jalankan `php artisan voyager:setup-permissions`.
  - Cek role & permission di Voyager admin.
- **Role tidak ditemukan:**
  - Jalankan seeder `RolesTableSeeder`.
- **AI Grading gagal:**
  - Cek API Key Gemini, koneksi internet, dan log error.
- **Tampilan tidak update:**
  - Jalankan `php artisan view:clear` dan refresh browser.

---

## Kontribusi
- Pull request & issue sangat diterima.
- Ikuti standar PSR-12 dan best practice Laravel.
- Dokumentasi kode & komentar sangat dianjurkan.

---

## Lisensi
MIT. Lihat file LICENSE.


## To call PHP Artisan in Server
/usr/local/lsws/lsphp82/bin/php artisan 