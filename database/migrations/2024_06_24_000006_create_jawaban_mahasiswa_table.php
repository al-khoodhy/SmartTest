<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tugas_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->longText('jawaban')->nullable();
            $table->datetime('waktu_mulai');
            $table->datetime('waktu_selesai')->nullable();
            $table->enum('status', ['draft', 'submitted', 'graded'])->default('draft');
            $table->integer('durasi_detik')->nullable();
            $table->timestamps();

            $table->foreign('tugas_id')->references('id')->on('tugas')->onDelete('cascade');
            $table->foreign('mahasiswa_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['tugas_id', 'mahasiswa_id']);
            $table->index(['mahasiswa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_mahasiswa');
    }
}; 