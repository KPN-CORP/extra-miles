<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menutup selisih antara skema dan kode untuk tabel `news` dan `quotes`.
 *
 * Migrasi pembuatnya hanya membuat `id` + timestamps, padahal `App\Models\News`
 * dan `App\Models\Quotes` (beserta endpoint API dan halaman adminnya) sudah
 * lama memakai kolom-kolom di bawah ini. Artinya di server kolom itu pernah
 * ditambahkan manual, sehingga database baru dari `migrate:fresh` tidak bisa
 * dipakai sama sekali.
 *
 * Polanya mengikuti 2026_09_03_110000_add_missing_columns_to_event_tables:
 * tambah hanya kalau belum ada, supaya aman dijalankan di database lama yang
 * kolomnya sudah lengkap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $this->addIfMissing('news', 'category', fn () => $table->string('category', 50)->nullable()->after('id'));
            $this->addIfMissing('news', 'title', fn () => $table->string('title', 255)->nullable()->after('category'));
            $this->addIfMissing('news', 'publish_date', fn () => $table->date('publish_date')->nullable());
            $this->addIfMissing('news', 'content', fn () => $table->longText('content')->nullable());
            $this->addIfMissing('news', 'image', fn () => $table->string('image', 255)->nullable());
            $this->addIfMissing('news', 'link', fn () => $table->string('link', 255)->nullable());
            $this->addIfMissing('news', 'businessUnit', fn () => $table->json('businessUnit')->nullable());
            $this->addIfMissing('news', 'status', fn () => $table->string('status', 50)->nullable());
            $this->addIfMissing('news', 'created_by', fn () => $table->unsignedBigInteger('created_by')->nullable());
            $this->addIfMissing('news', 'deleted_at', fn () => $table->softDeletes());
        });

        // publish_date menentukan urutan listing (orderBy desc) dan dipakai
        // endpoint dashboard dengan limit, jadi layak diindeks.
        if (Schema::hasColumn('news', 'publish_date') && ! $this->hasIndex('news', 'news_publish_date_index')) {
            Schema::table('news', function (Blueprint $table) {
                $table->index('publish_date', 'news_publish_date_index');
            });
        }

        Schema::table('quotes', function (Blueprint $table) {
            $this->addIfMissing('quotes', 'quotes', fn () => $table->text('quotes')->nullable()->after('id'));
            $this->addIfMissing('quotes', 'author', fn () => $table->string('author', 255)->nullable());
            $this->addIfMissing('quotes', 'created_by', fn () => $table->unsignedBigInteger('created_by')->nullable());
            $this->addIfMissing('quotes', 'deleted_at', fn () => $table->softDeletes());
        });
    }

    public function down(): void
    {
        if ($this->hasIndex('news', 'news_publish_date_index')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropIndex('news_publish_date_index');
            });
        }

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(array_values(array_filter(
                ['category', 'title', 'publish_date', 'content', 'image', 'link', 'businessUnit', 'status', 'created_by', 'deleted_at'],
                fn ($c) => Schema::hasColumn('news', $c)
            )));
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(array_values(array_filter(
                ['quotes', 'author', 'created_by', 'deleted_at'],
                fn ($c) => Schema::hasColumn('quotes', $c)
            )));
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
