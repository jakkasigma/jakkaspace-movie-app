<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'subscription_tier')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('subscription_tier', 20)->default('free')->after('has_password');
                $table->timestamp('subscribed_at')->nullable()->after('subscription_tier');
                $table->timestamp('expires_at')->nullable()->after('subscribed_at');
                $table->unsignedBigInteger('theme_id')->nullable()->after('expires_at');
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('theme_id')->references('id')->on('themes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['theme_id']);
            $table->dropColumn(['subscription_tier', 'subscribed_at', 'expires_at', 'theme_id']);
        });
    }
};
