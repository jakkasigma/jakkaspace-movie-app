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
        Schema::table('list_messages', function (Blueprint $table): void {
            $table->string('type', 20)->default('message')->after('message');
            $table->json('metadata')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('list_messages', function (Blueprint $table): void {
            $table->dropColumn(['type', 'metadata']);
        });
    }
};
