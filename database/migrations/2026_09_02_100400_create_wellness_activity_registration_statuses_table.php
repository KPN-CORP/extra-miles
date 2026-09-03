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
        Schema::create('wellness_activity_registration_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wellness_activity_registration_id');

            $table->string('from_status', 30)->nullable();
            $table->string('status', 30);
            $table->dateTime('changed_at');
            $table->text('remark')->nullable();

            // Null when the transition was made by the system (waitlist promotion).
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Append-only audit trail: no soft deletes, no updates.

            $table->foreign('wellness_activity_registration_id', 'wars_registration_foreign')
                ->references('id')->on('wellness_activity_registrations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index(['wellness_activity_registration_id', 'changed_at'], 'wars_registration_changed_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_activity_registration_statuses');
    }
};
