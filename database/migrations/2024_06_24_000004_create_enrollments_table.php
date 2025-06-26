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
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->date('enrolled_at');
            $table->dateTime('tanggal_daftar')->nullable();
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mata_kuliah_id')->references('id')->on('mata_kuliah')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->unique(['mahasiswa_id', 'mata_kuliah_id']);
            $table->index(['mata_kuliah_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
}; 