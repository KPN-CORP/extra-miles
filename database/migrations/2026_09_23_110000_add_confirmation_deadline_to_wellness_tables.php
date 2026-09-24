<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A seat can now be granted without being taken: the employee has to confirm
     * it before a deadline, or it is revoked and passed down the queue.
     *
     * `confirmation_deadline` is per schedule and nullable -- a schedule without
     * one behaves exactly as before, confirming outright with no extra step.
     *
     * `confirm_due_at` is per registration because the two are not the same: an
     * employee promoted off the queue *after* the schedule's deadline has passed
     * still gets a window of their own, running to the session start. Storing it
     * on the row also lets the sweep that revokes expired seats find them with an
     * index instead of a join.
     */
    public function up(): void
    {
        Schema::table('wellness_activity_schedules', function (Blueprint $table) {
            $table->dateTime('confirmation_deadline')->nullable()->after('registration_end_at');
        });

        Schema::table('wellness_activity_registrations', function (Blueprint $table) {
            $table->dateTime('confirm_due_at')->nullable()->after('registered_at');

            $table->index(['status', 'confirm_due_at'], 'war_status_confirm_due_index');
        });
    }

    public function down(): void
    {
        Schema::table('wellness_activity_registrations', function (Blueprint $table) {
            $table->dropIndex('war_status_confirm_due_index');
            $table->dropColumn('confirm_due_at');
        });

        Schema::table('wellness_activity_schedules', function (Blueprint $table) {
            $table->dropColumn('confirmation_deadline');
        });
    }
};
