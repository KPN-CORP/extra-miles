<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * The attendance QR moves from the session to the activity type: one printed
     * code per type, reused by every session under it. The scan window that used
     * to be a global config value becomes a per-type setting, so a type can allow
     * a wider or tighter window than the default.
     */
    public function up(): void
    {
        Schema::table('wellness_activity_types', function (Blueprint $table) {
            // Nullable for now -- filled in below, then locked down.
            $table->uuid('qr_token')->nullable()->after('is_active');

            // Null means "use the config default"; keeps existing types behaving
            // exactly as they did before this migration ran.
            $table->unsignedSmallInteger('check_in_opens_minutes_before')->nullable()->after('qr_token');
            $table->unsignedSmallInteger('check_in_closes_minutes_after')->nullable()->after('check_in_opens_minutes_before');
        });

        // withTrashed on purpose: an archived type can still be restored, and a
        // null token would then break its creating-hook-free update path.
        foreach (DB::table('wellness_activity_types')->select('id')->get() as $type) {
            DB::table('wellness_activity_types')
                ->where('id', $type->id)
                ->update(['qr_token' => (string) Str::uuid()]);
        }

        Schema::table('wellness_activity_types', function (Blueprint $table) {
            $table->uuid('qr_token')->nullable(false)->change();
            $table->unique('qr_token', 'wat_qr_token_unique');
        });

        Schema::table('wellness_activity_schedules', function (Blueprint $table) {
            $table->dropUnique('was_qr_token_unique');
            $table->dropColumn('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('wellness_activity_schedules', function (Blueprint $table) {
            $table->uuid('qr_token')->nullable()->after('status');
        });

        foreach (DB::table('wellness_activity_schedules')->select('id')->get() as $schedule) {
            DB::table('wellness_activity_schedules')
                ->where('id', $schedule->id)
                ->update(['qr_token' => (string) Str::uuid()]);
        }

        Schema::table('wellness_activity_schedules', function (Blueprint $table) {
            $table->uuid('qr_token')->nullable(false)->change();
            $table->unique('qr_token', 'was_qr_token_unique');
        });

        Schema::table('wellness_activity_types', function (Blueprint $table) {
            $table->dropUnique('wat_qr_token_unique');
            $table->dropColumn(['qr_token', 'check_in_opens_minutes_before', 'check_in_closes_minutes_after']);
        });
    }
};
