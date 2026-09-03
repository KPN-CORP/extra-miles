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
        Schema::create('wellness_activity_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wellness_activity_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('location', 150)->nullable();

            // null quota = unlimited seats
            $table->unsignedInteger('quota')->nullable();

            $table->dateTime('registration_start_at')->nullable();
            $table->dateTime('registration_end_at')->nullable();

            $table->string('status', 30)->default('open');

            // Attendance QR: a rotatable token, never a stored image.
            $table->uuid('qr_token')->unique('was_qr_token_unique');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wellness_activity_id', 'was_activity_foreign')
                ->references('id')->on('wellness_activities')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Lets wellness_activity_registrations carry a composite foreign key on
            // (schedule_id, activity_id), so a registration can never point at a
            // schedule that belongs to a different activity.
            $table->unique(['id', 'wellness_activity_id'], 'was_id_activity_unique');

            $table->index(['wellness_activity_id', 'start_at'], 'was_activity_start_index');
            $table->index(['status', 'start_at'], 'was_status_start_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_activity_schedules');
    }
};
