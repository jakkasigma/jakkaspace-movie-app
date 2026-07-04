<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('google_id')->nullable()->unique()->after('username');
            $table->string('avatar_url')->nullable()->after('avatar')
                ->comment('URL avatar dari provider OAuth, berbeda dari avatar upload lokal');
            $table->boolean('has_password')->default(true)->after('password')
                ->comment('False jika user daftar via Google tanpa set password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['google_id', 'avatar_url', 'has_password']);
        });
    }
};
