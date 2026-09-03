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
        Schema::table('wellness_activities', function (Blueprint $table) {
            // How seats are handed out: 'fifo' auto-confirms while the quota
            // lasts, 'selection' leaves everyone waiting for an admin to pick.
            $table->string('registration_method', 20)
                ->default('fifo')
                ->after('wellness_activity_type_id');

            $table->index(['registration_method', 'status'], 'wa_method_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wellness_activities', function (Blueprint $table) {
            $table->dropIndex('wa_method_status_index');
            $table->dropColumn('registration_method');
        });
    }
};
