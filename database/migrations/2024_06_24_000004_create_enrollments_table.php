<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->date('enrolled_at');
            $table->dateTime('tanggal_daftar')->nullable();
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->unique(['mahasiswa_id', 'kelas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
}; 