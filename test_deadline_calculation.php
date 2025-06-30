<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tugas;
use Carbon\Carbon;

echo "=== TEST PERHITUNGAN DEADLINE ===\n\n";

// Test 1: Periksa waktu sekarang dan timezone
echo "1. Informasi waktu saat ini:\n";
$now = Carbon::now();
echo "   ✅ Waktu sekarang: {$now->format('d/m/Y H:i:s')}\n";
echo "   ✅ Timezone: {$now->timezone->getName()}\n";
echo "   ✅ Timestamp: {$now->timestamp}\n";

// Test 2: Periksa tugas dengan deadline yang bermasalah
echo "\n2. Periksa tugas dengan deadline:\n";
$tugas = Tugas::with(['kelas.mataKuliah'])->first();

if ($tugas) {
    echo "   ✅ Tugas: {$tugas->judul}\n";
    echo "   ✅ Deadline: {$tugas->deadline->format('d/m/Y H:i:s')}\n";
    echo "   ✅ Deadline timezone: {$tugas->deadline->timezone->getName()}\n";
    echo "   ✅ Deadline timestamp: {$tugas->deadline->timestamp}\n";
    
    // Periksa apakah deadline sudah lewat
    $isExpired = $tugas->deadline < $now;
    echo "   ✅ Sudah expired: " . ($isExpired ? 'Ya' : 'Tidak') . "\n";
    
    // Periksa perhitungan diffForHumans
    $diffForHumans = $tugas->deadline->diffForHumans();
    echo "   ✅ diffForHumans: {$diffForHumans}\n";
    
    // Periksa perhitungan diffForHumans dengan parameter
    $diffForHumansWithParams = $tugas->deadline->diffForHumans(null, null, true);
    echo "   ✅ diffForHumans (with params): {$diffForHumansWithParams}\n";
    
    // Periksa selisih waktu dalam jam
    $diffInHours = $now->diffInHours($tugas->deadline, false);
    echo "   ✅ Selisih dalam jam: {$diffInHours} jam\n";
    
    // Periksa selisih waktu dalam menit
    $diffInMinutes = $now->diffInMinutes($tugas->deadline, false);
    echo "   ✅ Selisih dalam menit: {$diffInMinutes} menit\n";
    
    // Periksa apakah deadline sudah lewat dengan berbagai metode
    echo "\n3. Perbandingan metode pengecekan expired:\n";
    echo "   ✅ deadline < now(): " . ($tugas->deadline < $now ? 'Ya' : 'Tidak') . "\n";
    echo "   ✅ deadline->isPast(): " . ($tugas->deadline->isPast() ? 'Ya' : 'Tidak') . "\n";
    echo "   ✅ deadline->lt(now()): " . ($tugas->deadline->lt($now) ? 'Ya' : 'Tidak') . "\n";
    echo "   ✅ now()->gt(deadline): " . ($now->gt($tugas->deadline) ? 'Ya' : 'Tidak') . "\n";
    
    // Test dengan contoh deadline yang disebutkan user
    echo "\n4. Test dengan contoh deadline user (29/06/2025 07:59):\n";
    $testDeadline = Carbon::create(2025, 6, 29, 7, 59, 0);
    echo "   ✅ Test deadline: {$testDeadline->format('d/m/Y H:i:s')}\n";
    echo "   ✅ Sudah expired: " . ($testDeadline < $now ? 'Ya' : 'Tidak') . "\n";
    echo "   ✅ diffForHumans: {$testDeadline->diffForHumans()}\n";
    
    // Test dengan waktu sekarang + 5 jam
    echo "\n5. Test dengan deadline 5 jam dari sekarang:\n";
    $futureDeadline = $now->copy()->addHours(5);
    echo "   ✅ Future deadline: {$futureDeadline->format('d/m/Y H:i:s')}\n";
    echo "   ✅ Sudah expired: " . ($futureDeadline < $now ? 'Ya' : 'Tidak') . "\n";
    echo "   ✅ diffForHumans: {$futureDeadline->diffForHumans()}\n";
    
} else {
    echo "   ❌ Tidak ada tugas untuk ditest\n";
}

// Test 3: Periksa semua tugas yang expired
echo "\n6. Tugas yang sudah expired:\n";
$expiredTugas = Tugas::where('deadline', '<', $now)->get();
echo "   ✅ Total tugas expired: {$expiredTugas->count()}\n";

foreach ($expiredTugas as $tugas) {
    echo "   - {$tugas->judul}: {$tugas->deadline->format('d/m/Y H:i')} ({$tugas->deadline->diffForHumans()})\n";
}

// Test 4: Periksa semua tugas yang masih aktif
echo "\n7. Tugas yang masih aktif:\n";
$activeTugas = Tugas::where('deadline', '>', $now)->get();
echo "   ✅ Total tugas aktif: {$activeTugas->count()}\n";

foreach ($activeTugas as $tugas) {
    echo "   - {$tugas->judul}: {$tugas->deadline->format('d/m/Y H:i')} ({$tugas->deadline->diffForHumans()})\n";
}

echo "\n=== SELESAI TESTING DEADLINE ===\n";
echo "✅ Perhitungan deadline telah diperiksa\n";
echo "✅ Timezone dan timestamp telah diverifikasi\n";
echo "✅ Metode pengecekan expired telah dibandingkan\n"; 