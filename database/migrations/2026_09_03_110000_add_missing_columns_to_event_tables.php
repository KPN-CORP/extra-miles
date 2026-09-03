<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Brings the `events` / `event_participants` schema back in line with what the
 * Event code already expects.
 *
 * create_events_table only ever created 16 columns, but App\Models\Event
 * declares 11 more in $fillable and the controllers query them directly --
 * `where('category', 'EVO')` in EventController::evoIndex is the one that
 * surfaced this as "Unknown column 'category' in 'where clause'".
 *
 * Every column is added only when missing, so this is a no-op on any database
 * where they already exist (a server whose schema was patched by hand).
 */
return new class extends Migration
{
    /**
     * @var array<string, array<int, string>>
     */
    protected array $added = [];

    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $this->addIfMissing($table, 'events', 'category', fn () => $table->string('category', 50)->nullable()->after('title'));
            $this->addIfMissing($table, 'events', 'event_location', fn () => $table->string('event_location', 255)->nullable());
            // 'T' / 'F' flags set from checkboxes in EventController::store().
            $this->addIfMissing($table, 'events', 'status_survey', fn () => $table->string('status_survey', 5)->nullable());
            $this->addIfMissing($table, 'events', 'status_voting', fn () => $table->string('status_voting', 5)->nullable());
            $this->addIfMissing($table, 'events', 'quota', fn () => $table->unsignedInteger('quota')->nullable());
            $this->addIfMissing($table, 'events', 'regist_deadline', fn () => $table->date('regist_deadline')->nullable());
            // Targeting filters: written with json_encode() and read back with
            // whereJsonContains(), same as the existing businessUnit column.
            $this->addIfMissing($table, 'events', 'unit', fn () => $table->json('unit')->nullable());
            $this->addIfMissing($table, 'events', 'jobLevel', fn () => $table->json('jobLevel')->nullable());
            $this->addIfMissing($table, 'events', 'form_id', fn () => $table->unsignedBigInteger('form_id')->nullable());
            $this->addIfMissing($table, 'events', 'form_schema', fn () => $table->longText('form_schema')->nullable());
            $this->addIfMissing($table, 'events', 'barcode_token', fn () => $table->string('barcode_token', 36)->nullable());
        });

        if (in_array('category', $this->added['events'] ?? [], true)) {
            Schema::table('events', function (Blueprint $table) {
                $table->index('category', 'events_category_index');
            });
        }

        Schema::table('event_participants', function (Blueprint $table) {
            // Both are selected by App\Exports\ParticipantsExport.
            $this->addIfMissing($table, 'event_participants', 'messages', fn () => $table->text('messages')->nullable());
            $this->addIfMissing($table, 'event_participants', 'attending_at', fn () => $table->dateTime('attending_at')->nullable());
        });
    }

    public function down(): void
    {
        // Only drop what this migration actually created, so re-running down()
        // on a server that already had these columns leaves them alone.
        $events = array_values(array_intersect(
            $this->added['events'] ?? [],
            Schema::getColumnListing('events')
        ));

        if (in_array('category', $events, true)) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropIndex('events_category_index');
            });
        }

        if ($events) {
            Schema::table('events', function (Blueprint $table) use ($events) {
                $table->dropColumn($events);
            });
        }

        $participants = array_values(array_intersect(
            $this->added['event_participants'] ?? [],
            Schema::getColumnListing('event_participants')
        ));

        if ($participants) {
            Schema::table('event_participants', function (Blueprint $table) use ($participants) {
                $table->dropColumn($participants);
            });
        }
    }

    protected function addIfMissing(Blueprint $table, string $tableName, string $column, callable $define): void
    {
        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        $define();
        $this->added[$tableName][] = $column;
    }
};
