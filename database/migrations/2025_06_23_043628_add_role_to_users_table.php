<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_role', ['admin', 'dosen', 'mahasiswa'])->default('mahasiswa')->after('email');
            $table->string('nim_nip', 20)->nullable()->after('user_role');
            $table->string('phone', 15)->nullable()->after('nim_nip');
            $table->text('address')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('address');
            
            $table->index(['user_role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_role', 'is_active']);
            $table->dropColumn(['user_role', 'nim_nip', 'phone', 'address', 'is_active']);
        });
    }
};
