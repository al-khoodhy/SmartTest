<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jawaban_id');
            $table->decimal('nilai_ai', 5, 2)->nullable();
            $table->decimal('nilai_manual', 5, 2)->nullable();
            $table->decimal('nilai_final', 5, 2)->nullable();
            $table->text('feedback_ai')->nullable();
            $table->text('feedback_manual')->nullable();
            $table->json('detail_penilaian_ai')->nullable();
            $table->enum('status_penilaian', ['pending', 'ai_graded', 'manual_review', 'final'])->default('pending');
            $table->unsignedBigInteger('graded_by')->nullable();
            $table->datetime('graded_at')->nullable();
            $table->timestamps();

            $table->foreign('jawaban_id')->references('id')->on('jawaban_mahasiswa')->onDelete('cascade');
            $table->foreign('graded_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['jawaban_id']);
            $table->index(['status_penilaian']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
}; 