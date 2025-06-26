<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\MataKuliah;
use App\Models\Enrollment;
use App\Models\Tugas;
use App\Models\JawabanMahasiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user creation with valid data
     */
    public function test_user_can_be_created_with_valid_data(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'user_role' => 'mahasiswa',
            'nim_nip' => '123456789',
            'is_active' => true
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('mahasiswa', $user->user_role);
        $this->assertTrue($user->is_active);
    }

    /**
     * Test user role checking methods
     */
    public function test_user_role_checking_methods(): void
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $dosen = User::factory()->create(['user_role' => 'dosen']);
        $mahasiswa = User::factory()->create(['user_role' => 'mahasiswa']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isDosen());
        $this->assertFalse($admin->isMahasiswa());

        $this->assertFalse($dosen->isAdmin());
        $this->assertTrue($dosen->isDosen());
        $this->assertFalse($dosen->isMahasiswa());

        $this->assertFalse($mahasiswa->isAdmin());
        $this->assertFalse($mahasiswa->isDosen());
        $this->assertTrue($mahasiswa->isMahasiswa());
    }

    /**
     * Test mahasiswa enrollment relationship
     */
    public function test_mahasiswa_enrollment_relationship(): void
    {
        $mahasiswa = User::factory()->create(['user_role' => 'mahasiswa']);
        $mataKuliah = MataKuliah::factory()->create();

        $enrollment = Enrollment::create([
            'mahasiswa_id' => $mahasiswa->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'status' => 'active',
            'tanggal_daftar' => now(),
            'enrolled_at' => now()
        ]);

        $this->assertTrue($mahasiswa->enrollments()->exists());
        $this->assertEquals(1, $mahasiswa->enrollments()->count());
        $this->assertEquals($mataKuliah->id, $mahasiswa->enrollments()->first()->mata_kuliah_id);
    }

    /**
     * Test dosen mata kuliah relationship
     */
    public function test_dosen_mata_kuliah_relationship(): void
    {
        $dosen = User::factory()->create(['user_role' => 'dosen']);
        $mataKuliah = MataKuliah::factory()->create(['dosen_id' => $dosen->id]);

        $this->assertTrue($dosen->mataKuliahDiampu()->exists());
        $this->assertEquals(1, $dosen->mataKuliahDiampu()->count());
        $this->assertEquals($mataKuliah->id, $dosen->mataKuliahDiampu()->first()->id);
    }

    /**
     * Test dosen tugas relationship
     */
    public function test_dosen_tugas_relationship(): void
    {
        $dosen = User::factory()->create(['user_role' => 'dosen']);
        $mataKuliah = MataKuliah::factory()->create(['dosen_id' => $dosen->id]);
        $tugas = Tugas::factory()->create([
            'mata_kuliah_id' => $mataKuliah->id,
            'dosen_id' => $dosen->id
        ]);

        $this->assertTrue($dosen->tugasDibuat()->exists());
        $this->assertEquals(1, $dosen->tugasDibuat()->count());
        $this->assertEquals($tugas->id, $dosen->tugasDibuat()->first()->id);
    }

    /**
     * Test mahasiswa jawaban relationship
     */
    public function test_mahasiswa_jawaban_relationship(): void
    {
        $mahasiswa = User::factory()->create(['user_role' => 'mahasiswa']);
        $dosen = User::factory()->create(['user_role' => 'dosen']);
        $mataKuliah = MataKuliah::factory()->create(['dosen_id' => $dosen->id]);
        $tugas = Tugas::factory()->create([
            'mata_kuliah_id' => $mataKuliah->id,
            'dosen_id' => $dosen->id
        ]);

        $jawaban = JawabanMahasiswa::create([
            'tugas_id' => $tugas->id,
            'mahasiswa_id' => $mahasiswa->id,
            'jawaban' => 'Test jawaban',
            'waktu_mulai' => now(),
            'status' => 'draft'
        ]);

        $this->assertTrue($mahasiswa->jawabanMahasiswa()->exists());
        $this->assertEquals(1, $mahasiswa->jawabanMahasiswa()->count());
        $this->assertEquals($jawaban->id, $mahasiswa->jawabanMahasiswa()->first()->id);
    }

    /**
     * Test user active scope
     */
    public function test_user_active_scope(): void
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);

        $activeUsers = User::active()->get();
        $this->assertEquals(1, $activeUsers->count());
        $this->assertTrue($activeUsers->first()->is_active);
    }

    /**
     * Test user role filtering
     */
    public function test_user_role_filtering(): void
    {
        User::factory()->create(['user_role' => 'admin']);
        User::factory()->create(['user_role' => 'dosen']);
        User::factory()->create(['user_role' => 'mahasiswa']);

        $admins = User::where('user_role', 'admin')->get();
        $dosens = User::where('user_role', 'dosen')->get();
        $mahasiswas = User::where('user_role', 'mahasiswa')->get();

        $this->assertEquals(1, $admins->count());
        $this->assertEquals(1, $dosens->count());
        $this->assertEquals(1, $mahasiswas->count());

        $this->assertEquals('admin', $admins->first()->user_role);
        $this->assertEquals('dosen', $dosens->first()->user_role);
        $this->assertEquals('mahasiswa', $mahasiswas->first()->user_role);
    }

    /**
     * Test password hashing
     */
    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext_password'
        ]);

        $this->assertNotEquals('plaintext_password', $user->password);
        $this->assertTrue(Hash::check('plaintext_password', $user->password));
    }

    /**
     * Test user fillable attributes
     */
    public function test_user_fillable_attributes(): void
    {
        $fillable = [
            'name', 'email', 'password', 'user_role', 'nim_nip', 
            'phone', 'address', 'is_active'
        ];

        $user = new User();
        $this->assertEquals($fillable, $user->getFillable());
    }

    /**
     * Test user hidden attributes
     */
    public function test_user_hidden_attributes(): void
    {
        $hidden = ['password', 'remember_token'];

        $user = new User();
        $this->assertEquals($hidden, $user->getHidden());
    }
}
