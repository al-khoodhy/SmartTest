<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('rubrik_penilaian')->nullable();
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->datetime('deadline');
            $table->integer('durasi_menit')->default(120);
            $table->integer('nilai_maksimal')->default(100);
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_grade')->default(true);
            $table->timestamps();

            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
}; 