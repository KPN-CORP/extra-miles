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
        Schema::create('wellness_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wellness_activity_type_id');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('image', 100)->nullable();
            $table->string('status', 30)->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wellness_activity_type_id', 'wa_type_foreign')
                ->references('id')->on('wellness_activity_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(['wellness_activity_type_id', 'status'], 'wa_type_status_index');
            $table->index('status', 'wa_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_activities');
    }
};
