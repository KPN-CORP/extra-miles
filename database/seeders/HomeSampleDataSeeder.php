<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data contoh untuk beranda aplikasi karyawan.
 *
 * Dipakai untuk melihat beranda dalam keadaan terisi -- kartu event, berita,
 * kutipan -- bukan hanya empty state. Hanya menulis ke koneksi `mysql`
 * (database aplikasi ini); tabel `kpncorp` sama sekali tidak disentuh, hanya
 * dibaca untuk mencari nama karyawan.
 *
 * Idempoten: tiap baris dikunci dengan judul/teks yang unik lewat
 * updateOrInsert, jadi menjalankannya berulang tidak menumpuk duplikat.
 *
 * Employee ID tujuannya bisa ditentukan lewat env SEED_EMPLOYEE_ID; kalau tidak
 * diisi, dipakai nilai default di bawah (akun yang biasa dipakai menguji).
 */
class HomeSampleDataSeeder extends Seeder
{
    private const DEFAULT_EMPLOYEE_ID = '01126010024';

    public function run(): void
    {
        $employeeId = env('SEED_EMPLOYEE_ID', self::DEFAULT_EMPLOYEE_ID);
        $fullname = $this->lookupFullname($employeeId);
        $today = Carbon::today();

        $this->command?->info("Seeding home sample data for {$employeeId} ({$fullname})");

        // ── Events ───────────────────────────────────────────────────────────
        // Statusnya dipilih supaya tiap cabang di ActivitySection kelihatan:
        // satu menunggu konfirmasi, satu bisa dipindai hari ini, satu belum
        // waktunya, satu sudah tercatat hadir.
        $events = [
            [
                'title' => 'Blood Donor Day',
                'category' => 'Event',
                'description' => 'Donor darah rutin bersama PMI di lobi kantor.',
                'start_date' => $today->copy()->addDays(9)->toDateString(),
                'end_date' => $today->copy()->addDays(9)->toDateString(),
                'time_start' => '09:00:00',
                'time_end' => '12:00:00',
                'event_location' => '3F Gama Tower',
                'status' => 'Open Registration',
                'quota' => 80,
                'regist_deadline' => $today->copy()->addDays(6)->toDateString(),
                'participant' => ['status' => 'Confirmation', 'attending_status' => null],
            ],
            [
                // Dua baris berikut menaikkan jumlah menunggu konfirmasi jadi 3,
                // supaya batas 2 kartu + tombol "Show All" benar-benar terlihat.
                'title' => 'Town Hall Semester 2',
                'category' => 'Event',
                'description' => 'Town hall semesteran bersama jajaran direksi.',
                'start_date' => $today->copy()->addDays(4)->toDateString(),
                'end_date' => $today->copy()->addDays(4)->toDateString(),
                'time_start' => '10:00:00',
                'time_end' => '12:00:00',
                'event_location' => 'Auditorium Gama Tower',
                'status' => 'Open Registration',
                'quota' => 400,
                'regist_deadline' => $today->copy()->addDays(2)->toDateString(),
                'participant' => ['status' => 'Confirmation', 'attending_status' => null],
            ],
            [
                'title' => 'Vaccination Day Batch 2',
                'category' => 'Event',
                'description' => 'Vaksinasi lanjutan bagi karyawan dan keluarga.',
                'start_date' => $today->copy()->addDays(14)->toDateString(),
                'end_date' => $today->copy()->addDays(14)->toDateString(),
                'time_start' => '08:30:00',
                'time_end' => '11:30:00',
                'event_location' => '2F Gama Tower',
                'status' => 'Open Registration',
                'quota' => 150,
                'regist_deadline' => $today->copy()->addDays(11)->toDateString(),
                'participant' => ['status' => 'Confirmation', 'attending_status' => null],
            ],
            [
                'title' => 'KPN Breakfasting 2026',
                'category' => 'Event',
                'description' => 'Buka bersama seluruh karyawan KPN Corporation.',
                'start_date' => $today->toDateString(),
                'end_date' => $today->toDateString(),
                'time_start' => '15:30:00',
                'time_end' => '18:30:00',
                'event_location' => '3F Gama Tower',
                'status' => 'Open Registration',
                'quota' => 250,
                'regist_deadline' => $today->copy()->subDays(2)->toDateString(),
                'participant' => ['status' => 'Registered', 'attending_status' => null],
            ],
            [
                'title' => 'Leadership Sharing Session',
                'category' => 'Event',
                'description' => 'Sesi berbagi pengalaman dari jajaran leader.',
                'start_date' => $today->copy()->addDays(5)->toDateString(),
                'end_date' => $today->copy()->addDays(5)->toDateString(),
                'time_start' => '13:00:00',
                'time_end' => '15:00:00',
                'event_location' => 'Auditorium Gama Tower',
                'status' => 'Open Registration',
                'quota' => 120,
                'regist_deadline' => $today->copy()->addDays(3)->toDateString(),
                'participant' => ['status' => 'Registered', 'attending_status' => null],
            ],
            [
                'title' => 'Fun Walk Extra Mile',
                'category' => 'Event',
                'description' => 'Jalan sehat bersama keluarga karyawan.',
                'start_date' => $today->toDateString(),
                'end_date' => $today->copy()->addDay()->toDateString(),
                'time_start' => '06:30:00',
                'time_end' => '09:00:00',
                'event_location' => 'Lapangan Senayan',
                'status' => 'Full Booked',
                'quota' => 300,
                'regist_deadline' => $today->copy()->subDays(4)->toDateString(),
                'participant' => ['status' => 'Registered', 'attending_status' => 'Attending'],
            ],
            [
                // category EVO dikecualikan dari /api/my-event, jadi ini hanya
                // mengisi halaman /evo -- bukan bagian daftar event beranda.
                'title' => 'EVO Program Batch 3',
                'category' => 'EVO',
                'description' => 'Program pengembangan talenta internal KPN.',
                'start_date' => $today->copy()->addDays(21)->toDateString(),
                'end_date' => $today->copy()->addDays(23)->toDateString(),
                'time_start' => '08:00:00',
                'time_end' => '17:00:00',
                'event_location' => 'Training Center Gama Tower',
                'status' => 'Open Registration',
                'quota' => 40,
                'regist_deadline' => $today->copy()->addDays(14)->toDateString(),
                'participant' => null,
            ],
        ];

        foreach ($events as $row) {
            $participant = $row['participant'];
            unset($row['participant']);

            $row['businessUnit'] = json_encode(['KPN Corporation']);
            $row['barcode_token'] = (string) Str::uuid();
            $row['created_by'] = 1;
            $row['updated_at'] = now();

            DB::table('events')->updateOrInsert(
                ['title' => $row['title']],
                $row + ['created_at' => now()]
            );

            if ($participant === null) {
                continue;
            }

            $eventId = DB::table('events')->where('title', $row['title'])->value('id');

            DB::table('event_participants')->updateOrInsert(
                ['event_id' => $eventId, 'employee_id' => $employeeId],
                [
                    'fullname' => $fullname,
                    'business_unit' => 'KPN Corporation',
                    'status' => $participant['status'],
                    'attending_status' => $participant['attending_status'],
                    'attending_at' => $participant['attending_status'] ? now() : null,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // ── News ─────────────────────────────────────────────────────────────
        // Gambarnya menunjuk ke berkas yang benar-benar ada di
        // storage/app/public, karena SPA memuatnya lewat /storage/<image>.
        $news = [
            [
                'title' => 'Inovasi Dimulai dari Empati',
                'publish_date' => $today->copy()->subDays(1)->toDateString(),
                'image' => 'assets/images/sample-news-1.jpg',
                'content' => '<p>Inovasi yang bertahan lama selalu berawal dari memahami masalah orang lain lebih dulu, bukan dari teknologinya.</p>',
            ],
            [
                'title' => 'Perkuat Sinergi Hadapi Kendala',
                'publish_date' => $today->copy()->subDays(2)->toDateString(),
                'image' => 'assets/images/sample-news-2.png',
                'content' => '<p>Kolaborasi lintas unit terbukti mempercepat penyelesaian kendala operasional di lapangan.</p>',
            ],
            [
                'title' => 'Budaya Aman Dimulai dari Diri Sendiri',
                'publish_date' => $today->copy()->subDays(5)->toDateString(),
                'image' => 'assets/images/sample-news-3.png',
                'content' => '<p>Keselamatan kerja bukan sekadar prosedur, tapi kebiasaan yang dibangun setiap hari.</p>',
            ],
        ];

        foreach ($news as $row) {
            DB::table('news')->updateOrInsert(
                ['title' => $row['title']],
                $row + [
                    'category' => 'Berita',
                    'status' => 'Publish',
                    'businessUnit' => json_encode(['KPN Corporation']),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // ── Banner aktivitas wellness ────────────────────────────────────────
        // Aktivitasnya sendiri tidak dibuat di sini (sudah ada di database);
        // yang diisi hanya kolom image yang masih NULL, supaya banner di halaman
        // detail wellness ada isinya. Berkasnya dilayani lewat GET /images/{file}
        // dari storage/app/public, bukan lewat /storage.
        $banners = [
            'Morning Yoga' => 'wellness-morning-yoga.png',
            'Gym Ceria' => 'wellness-gym-ceria.png',
        ];

        foreach ($banners as $name => $image) {
            $filled = DB::table('wellness_activities')
                ->where('name', $name)
                ->whereNull('image')
                ->update(['image' => $image, 'updated_at' => now()]);

            if ($filled === 0) {
                $this->command?->line("  wellness banner for {$name}: skipped (missing or already set)");
            }
        }

        // ── Quote ────────────────────────────────────────────────────────────
        // Endpoint kutipan hanya mengambil satu baris terbaru (orderBy id desc).
        DB::table('quotes')->updateOrInsert(
            ['quotes' => 'Kamu tidak harus hebat untuk memulai, tapi kamu harus memulai untuk menjadi hebat.'],
            [
                'author' => 'Tiara Arletta (KPN Downstream Tanjung Pura)',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command?->info('Done: '.count($events).' events, '.count($news).' news, 1 quote.');
    }

    /**
     * Nama karyawan dibaca dari kpncorp supaya kartu peserta menampilkan nama
     * yang benar. Read-only, dan kalau koneksinya tidak tersedia seeder tetap
     * jalan dengan nama placeholder.
     */
    private function lookupFullname(string $employeeId): string
    {
        try {
            return DB::connection('kpncorp')->table('employees')
                ->where('employee_id', $employeeId)
                ->value('fullname') ?? $employeeId;
        } catch (\Throwable $e) {
            $this->command?->warn('kpncorp unreachable, using employee id as name.');

            return $employeeId;
        }
    }
}
