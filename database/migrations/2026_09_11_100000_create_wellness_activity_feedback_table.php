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
        Schema::create('wellness_activity_feedback', function (Blueprint $table) {
            $table->id();

            // Feedback hangs off the registration, not the employee: only someone
            // who actually attended the session may leave it, and the registration
            // is what records that.
            $table->unsignedBigInteger('wellness_activity_registration_id');

            // Denormalised the same way registrations are, so the admin list can
            // filter by schedule or activity without a second hop. The composite
            // foreign key below keeps the pair from ever drifting.
            $table->unsignedBigInteger('wellness_activity_schedule_id');
            $table->unsignedBigInteger('wellness_activity_id');

            // Employees live in the `kpncorp` connection, so no foreign key is
            // possible. The name is a snapshot, as on registrations.
            $table->string('employee_id', 50);
            $table->string('fullname', 150)->nullable();

            $table->text('message');
            $table->dateTime('submitted_at');

            $table->timestamps();

            // No soft deletes: feedback is what the employee said, and the admin
            // side only ever reads it.

            $table->foreign('wellness_activity_registration_id', 'waf_registration_foreign')
                ->references('id')->on('wellness_activity_registrations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign(['wellness_activity_schedule_id', 'wellness_activity_id'], 'waf_schedule_activity_foreign')
                ->references(['id', 'wellness_activity_id'])->on('wellness_activity_schedules')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // One feedback per registration, and registrations are already unique
            // per (schedule, employee) -- so this is also "one per employee per
            // session". Resubmitting updates the row in place.
            $table->unique('wellness_activity_registration_id', 'waf_registration_unique');

            $table->index(['wellness_activity_schedule_id', 'submitted_at'], 'waf_schedule_submitted_index');
            $table->index(['wellness_activity_id', 'submitted_at'], 'waf_activity_submitted_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_activity_feedback');
    }
};
