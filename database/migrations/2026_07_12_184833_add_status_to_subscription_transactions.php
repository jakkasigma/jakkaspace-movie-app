<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_transactions', function (Blueprint $table): void {
            $table->string('status', 20)->default('pending')->after('payment_method');
            $table->string('snap_token', 255)->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('expires_at');
            $table->string('snap_url', 255)->nullable()->after('snap_token');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_transactions', function (Blueprint $table): void {
            $table->dropColumn(['status', 'snap_token', 'paid_at', 'snap_url']);
        });
    }
};
