<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

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
