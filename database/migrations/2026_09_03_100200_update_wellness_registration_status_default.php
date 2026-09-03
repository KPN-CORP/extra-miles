<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The status vocabulary changed with the FIFO / Selection-by-Admin methods
     * ('pending' and 'approved' no longer exist), so the column default follows.
     * The service always sets the status explicitly; this just stops the schema
     * from advertising a value the enum cannot parse.
     */
    public function up(): void
    {
        Schema::table('wellness_activity_registrations', function (Blueprint $table) {
            $table->string('status', 30)->default('registered')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wellness_activity_registrations', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });
    }
};
