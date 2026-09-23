<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A repeating schedule is stored as plain rows -- one per occurrence -- tied
     * together by `recurrence_group_id`. Materialising the occurrences keeps
     * every existing query (seat counts, registrations, check-in) working
     * unchanged, at the cost of writing several rows on create.
     *
     * The frequency and the until date are kept on each row so the series can
     * describe itself in the UI without a separate table.
     */
    public function up(): void
    {
        Schema::table('wellness_activity_schedules', function (Blueprint $table) {
            $table->uuid('recurrence_group_id')->nullable()->after('status');
            $table->string('recurrence_frequency', 20)->nullable()->after('recurrence_group_id');
            $table->date('recurrence_until')->nullable()->after('recurrence_frequency');

            $table->index(['recurrence_group_id', 'start_at'], 'was_recurrence_start_index');
        });
    }

    public function down(): void
    {
        Schema::table('wellness_activity_schedules', function (Blueprint $table) {
            $table->dropIndex('was_recurrence_start_index');
            $table->dropColumn(['recurrence_group_id', 'recurrence_frequency', 'recurrence_until']);
        });
    }
};
