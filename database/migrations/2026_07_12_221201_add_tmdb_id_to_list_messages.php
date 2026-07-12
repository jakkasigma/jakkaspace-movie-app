<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('list_messages', function (Blueprint $table): void {
            $table->unsignedBigInteger('tmdb_id')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('list_messages', function (Blueprint $table): void {
            $table->dropColumn('tmdb_id');
        });
    }
};
