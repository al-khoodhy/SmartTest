<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_soal_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jawaban_mahasiswa_id');
            $table->unsignedBigInteger('soal_id');
            $table->longText('jawaban')->nullable();
            $table->datetime('waktu_mulai')->nullable();
            $table->datetime('waktu_selesai')->nullable();
            $table->enum('status', ['draft', 'submitted', 'graded'])->default('draft');
            $table->timestamps();

            $table->foreign('jawaban_mahasiswa_id')->references('id')->on('jawaban_mahasiswa')->onDelete('cascade');
            $table->foreign('soal_id')->references('id')->on('soal')->onDelete('cascade');
            $table->unique(['jawaban_mahasiswa_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_soal_mahasiswa');
    }
}; 