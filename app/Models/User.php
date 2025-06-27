<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends \TCG\Voyager\Models\User
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'nim_nip',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    // Relasi untuk Dosen - Mata Kuliah yang diampu
    public function mataKuliahDiampu()
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_user', 'user_id', 'mata_kuliah_id');
    }
    
    // Relasi untuk Dosen - Tugas yang dibuat
    public function tugasDibuat()
    {
        return $this->hasMany(Tugas::class, 'dosen_id');
    }
    
    // Relasi untuk Mahasiswa - Enrollment
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'mahasiswa_id');
    }
    
    // Relasi untuk Mahasiswa - Jawaban yang dibuat
    public function jawabanMahasiswa()
    {
        return $this->hasMany(JawabanMahasiswa::class, 'mahasiswa_id');
    }
    
    // Relasi untuk Grader - Penilaian yang dilakukan
    public function penilaianDilakukan()
    {
        return $this->hasMany(Penilaian::class, 'graded_by');
    }
    
    // Relasi untuk Dosen - Kelas yang diampu
    public function kelasDiampu()
    {
        return $this->hasMany(Kelas::class, 'dosen_id');
    }
    
    // Relasi untuk Mahasiswa - Kelas yang diambil
    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_user', 'user_id', 'kelas_id');
    }
    
    // Scope untuk role tertentu
    public function scopeByRole($query, $role)
    {
        return $query->where('role_id', $role);
    }
    
    // Scope untuk user aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Check apakah user adalah admin
    public function isAdmin()
    {
        return $this->role_id == 1;
    }
    
    // Check apakah user adalah dosen
    public function isDosen()
    {
        return $this->role_id == 2;
    }
    
    // Check apakah user adalah mahasiswa
    public function isMahasiswa()
    {
        return $this->role_id == 3;
    }

}
