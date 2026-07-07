<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('redeem_codes_temp', function ($table) {
                $table->id();
                $table->string('type', 20)->default('free_access');
                $table->string('code', 16)->unique();
                $table->string('tier', 20);
                $table->string('discount_type', 10)->nullable();
                $table->unsignedSmallInteger('discount_value')->nullable();
                $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
                $table->string('popup_title', 100)->nullable();
                $table->text('popup_message')->nullable();
                $table->unsignedSmallInteger('duration_days')->nullable();
                $table->unsignedSmallInteger('max_uses')->default(1);
                $table->unsignedSmallInteger('used_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->constrained('users');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });

            DB::statement('INSERT INTO redeem_codes_temp SELECT id, \'free_access\', code, tier, NULL, NULL, NULL, NULL, NULL, duration_days, max_uses, used_count, is_active, created_by, expires_at, created_at, updated_at FROM redeem_codes');
            DB::statement('DROP TABLE redeem_codes');
            DB::statement('ALTER TABLE redeem_codes_temp RENAME TO redeem_codes');
        } else {
            Schema::table('redeem_codes', function ($table) {
                $table->string('type', 20)->default('free_access')->after('code');
                $table->string('discount_type', 10)->nullable()->after('tier');
                $table->unsignedSmallInteger('discount_value')->nullable()->after('discount_type');
                $table->foreignId('plan_id')->nullable()->after('discount_value')->constrained('subscription_plans')->nullOnDelete();
                $table->string('popup_title', 100)->nullable()->after('plan_id');
                $table->text('popup_message')->nullable()->after('popup_title');
                $table->unsignedSmallInteger('duration_days')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('redeem_codes', function ($table) {
            $table->dropColumn(['type', 'discount_type', 'discount_value', 'plan_id', 'popup_title', 'popup_message']);
            $table->unsignedSmallInteger('duration_days')->nullable(false)->change();
        });
    }
};
