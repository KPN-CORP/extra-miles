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
        Schema::create('wellness_activity_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wellness_activity_schedule_id');

            // Denormalised on purpose: admin list pages filter by activity constantly.
            // The composite foreign key below keeps it from ever drifting.
            $table->unsignedBigInteger('wellness_activity_id');

            // Employees live in the `kpncorp` connection, so no foreign key is
            // possible here. The HR fields below are a snapshot taken at
            // registration time -- people move units, reports must not.
            $table->string('employee_id', 50);
            $table->string('fullname', 150)->nullable();
            $table->string('business_unit', 100)->nullable();
            $table->string('unit', 100)->nullable();
            $table->string('job_level', 50)->nullable();
            $table->string('location', 150)->nullable();

            // Mirrors the newest wellness_activity_registration_statuses row.
            $table->string('status', 30)->default('pending');
            $table->string('source', 20)->default('self');

            $table->dateTime('registered_at');
            $table->dateTime('attended_at')->nullable();
            $table->string('attendance_note', 255)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // No soft deletes by design: cancelling is a status transition, and the
            // full trail lives in wellness_activity_registration_statuses. A nullable
            // deleted_at would also silently defeat the unique key below, because
            // MySQL treats every NULL as distinct.

            $table->foreign(['wellness_activity_schedule_id', 'wellness_activity_id'], 'war_schedule_activity_foreign')
                ->references(['id', 'wellness_activity_id'])->on('wellness_activity_schedules')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique(['wellness_activity_schedule_id', 'employee_id'], 'war_schedule_employee_unique');

            $table->index(['wellness_activity_schedule_id', 'status'], 'war_schedule_status_index');
            $table->index(['employee_id', 'status'], 'war_employee_status_index');
            $table->index(['wellness_activity_id', 'status'], 'war_activity_status_index');

            // Waitlist promotion reads this order under a row lock.
            $table->index(['wellness_activity_schedule_id', 'status', 'registered_at'], 'war_waitlist_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_activity_registrations');
    }
};
