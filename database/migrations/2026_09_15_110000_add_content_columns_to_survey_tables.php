<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menutup selisih skema untuk `surveys` dan `survey_participants`.
 *
 * Sama seperti `news` dan `quotes`: migrasi pembuatnya hanya membuat `id` +
 * timestamps, padahal model, admin controller dan endpoint API sudah memakai
 * kolom-kolom di bawah ini. Akibatnya `/api/survey-vote` melempar
 * "Unknown column 'status'" dan halaman /survey tidak pernah bisa memuat data
 * di database yang dibuat dari migrasi.
 *
 * Tipe JSON pada businessUnit/unit/jobLevel/location bukan pilihan bebas:
 * SurveyVoteController memfilternya dengan whereJsonLength dan
 * whereJsonContains.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $this->addIfMissing('surveys', 'category', fn () => $table->string('category', 50)->nullable()->after('id'));
            $this->addIfMissing('surveys', 'title', fn () => $table->string('title', 255)->nullable()->after('category'));
            $this->addIfMissing('surveys', 'description', fn () => $table->text('description')->nullable());
            $this->addIfMissing('surveys', 'event_id', fn () => $table->unsignedBigInteger('event_id')->nullable());
            $this->addIfMissing('surveys', 'form_id', fn () => $table->unsignedBigInteger('form_id')->nullable());
            $this->addIfMissing('surveys', 'form_schema', fn () => $table->longText('form_schema')->nullable());
            $this->addIfMissing('surveys', 'status', fn () => $table->string('status', 50)->nullable());
            $this->addIfMissing('surveys', 'banner', fn () => $table->string('banner', 255)->nullable());
            $this->addIfMissing('surveys', 'icon', fn () => $table->string('icon', 100)->nullable());
            $this->addIfMissing('surveys', 'start_date', fn () => $table->date('start_date')->nullable());
            $this->addIfMissing('surveys', 'end_date', fn () => $table->date('end_date')->nullable());
            $this->addIfMissing('surveys', 'time_start', fn () => $table->time('time_start')->nullable());
            $this->addIfMissing('surveys', 'time_end', fn () => $table->time('time_end')->nullable());
            $this->addIfMissing('surveys', 'content_link', fn () => $table->string('content_link', 255)->nullable());
            $this->addIfMissing('surveys', 'quota', fn () => $table->unsignedInteger('quota')->nullable());
            $this->addIfMissing('surveys', 'businessUnit', fn () => $table->json('businessUnit')->nullable());
            $this->addIfMissing('surveys', 'unit', fn () => $table->json('unit')->nullable());
            $this->addIfMissing('surveys', 'jobLevel', fn () => $table->json('jobLevel')->nullable());
            $this->addIfMissing('surveys', 'location', fn () => $table->json('location')->nullable());
            $this->addIfMissing('surveys', 'created_by', fn () => $table->unsignedBigInteger('created_by')->nullable());
            $this->addIfMissing('surveys', 'deleted_at', fn () => $table->softDeletes());
        });

        Schema::table('survey_participants', function (Blueprint $table) {
            $this->addIfMissing('survey_participants', 'survey_id', fn () => $table->unsignedBigInteger('survey_id')->nullable()->after('id'));
            $this->addIfMissing('survey_participants', 'employee_id', fn () => $table->string('employee_id')->nullable());
            $this->addIfMissing('survey_participants', 'fullname', fn () => $table->string('fullname')->nullable());
            $this->addIfMissing('survey_participants', 'status', fn () => $table->string('status')->nullable());
            $this->addIfMissing('survey_participants', 'form_id', fn () => $table->unsignedBigInteger('form_id')->nullable());
            $this->addIfMissing('survey_participants', 'form_data', fn () => $table->json('form_data')->nullable());
            $this->addIfMissing('survey_participants', 'job_level', fn () => $table->string('job_level')->nullable());
            $this->addIfMissing('survey_participants', 'unit', fn () => $table->string('unit')->nullable());
            $this->addIfMissing('survey_participants', 'business_unit', fn () => $table->string('business_unit')->nullable());
            $this->addIfMissing('survey_participants', 'location', fn () => $table->string('location')->nullable());
            $this->addIfMissing('survey_participants', 'created_by', fn () => $table->unsignedBigInteger('created_by')->nullable());
            $this->addIfMissing('survey_participants', 'updated_by', fn () => $table->unsignedBigInteger('updated_by')->nullable());
            $this->addIfMissing('survey_participants', 'deleted_at', fn () => $table->softDeletes());
        });

        // Endpoint daftar survei selalu memfilter status lalu rentang tanggal.
        if (! $this->hasIndex('surveys', 'surveys_status_start_date_index')) {
            Schema::table('surveys', function (Blueprint $table) {
                $table->index(['status', 'start_date'], 'surveys_status_start_date_index');
            });
        }

        // Peserta selalu dicari per survei + karyawan.
        if (! $this->hasIndex('survey_participants', 'survey_participants_survey_id_employee_id_index')) {
            Schema::table('survey_participants', function (Blueprint $table) {
                $table->index(['survey_id', 'employee_id'], 'survey_participants_survey_id_employee_id_index');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            ['surveys', 'surveys_status_start_date_index'],
            ['survey_participants', 'survey_participants_survey_id_employee_id_index'],
        ] as [$table, $index]) {
            if ($this->hasIndex($table, $index)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($index));
            }
        }

        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(array_values(array_filter([
                'category', 'title', 'description', 'event_id', 'form_id', 'form_schema', 'status',
                'banner', 'icon', 'start_date', 'end_date', 'time_start', 'time_end', 'content_link',
                'quota', 'businessUnit', 'unit', 'jobLevel', 'location', 'created_by', 'deleted_at',
            ], fn ($c) => Schema::hasColumn('surveys', $c))));
        });

        Schema::table('survey_participants', function (Blueprint $table) {
            $table->dropColumn(array_values(array_filter([
                'survey_id', 'employee_id', 'fullname', 'status', 'form_id', 'form_data', 'job_level',
                'unit', 'business_unit', 'location', 'created_by', 'updated_by', 'deleted_at',
            ], fn ($c) => Schema::hasColumn('survey_participants', $c))));
        });
    }

    private function addIfMissing(string $table, string $column, callable $add): void
    {
        if (! Schema::hasColumn($table, $column)) {
            $add();
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return count(Schema::getConnection()->select(
            'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$index]
        )) > 0;
    }
};
