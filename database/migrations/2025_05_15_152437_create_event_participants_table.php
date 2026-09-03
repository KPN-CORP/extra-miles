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
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('employee_id')->nullable();
            $table->string('fullname')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('job_level')->nullable();
            $table->string('location')->nullable();
            $table->string('unit')->nullable();
            $table->unsignedBigInteger('form_id')->nullable();
            $table->json('form_data')->nullable();
            $table->string('status')->nullable(); // e.g., Request, Approved, etc.
            $table->string('attending_status')->nullable();
            $table->string('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
