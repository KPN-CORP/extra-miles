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
        Schema::create('wellness_blacklists', function (Blueprint $table) {
            $table->id();

            // Employees live in the `kpncorp` connection, so no foreign key.
            // fullname is a snapshot, kept so the list stays readable.
            $table->string('employee_id', 50);
            $table->string('fullname', 150)->nullable();

            $table->text('reason');

            // Null means the blacklist does not expire on its own.
            $table->date('end_date')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Not unique on employee_id: an employee can be blacklisted, expire,
            // and be blacklisted again, and that history is worth keeping.
            $table->index(['employee_id', 'end_date'], 'wbl_employee_end_index');
            $table->index('end_date', 'wbl_end_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_blacklists');
    }
};
