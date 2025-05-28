<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $events = [
            [
                'id' => 1,
                'title' => "KPN Breakfasting 2025",
                'description' => "An annual breakfasting event organized by KPN Corporation to celebrate togetherness.",
                'start_date' => "2025-05-06",
                'end_date' => "2025-05-06",
                'time_start' => "15.30",
                'time_end' => "",
                'location' => "3F Gama Tower",
                'status' => "Open Registration",
                'image' => "event-img.png",
                'logo' => 'Logo-KPN.png',
                'businessUnit' => json_encode(["KPN Corporation", "Downstream"]),
                'created_by' => '123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'title' => "Blood Donor Day",
                'description' => "A blood donation drive to support health initiatives and save lives.",
                'start_date' => "2025-05-08",
                'end_date' => "2025-05-08",
                'time_start' => "09.00",
                'time_end' => "12.00",
                'location' => "3F Gama Tower",
                'status' => "Waiting List",
                'image' => "event-img.png",
                'logo' => 'Logo-KPN.png',
                'businessUnit' => json_encode([]),
                'created_by' => '123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'title' => "Andra Breakfast 2025",
                'description' => "A special breakfast event hosted at Andra Residences for networking and collaboration.",
                'start_date' => "2025-04-10",
                'end_date' => "2025-04-10",
                'time_start' => "15.30",
                'time_end' => "",
                'location' => "Andra Residences",
                'status' => "Full Booked",
                'image' => "event-img.png",
                'logo' => 'Logo-KPN.png',
                'businessUnit' => json_encode(["Downstream", "Property"]),
                'created_by' => '123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'title' => "Andra Day",
                'description' => "A day-long event celebrating achievements and milestones at Andra Residences.",
                'start_date' => "2025-05-10",
                'end_date' => "2025-05-10",
                'time_start' => "09.30",
                'time_end' => "16.00",
                'location' => "Andra Residences",
                'status' => "Registered",
                'image' => "event-img.png",
                'logo' => 'Logo-Property.png',
                'businessUnit' => json_encode(["Property"]),
                'created_by' => '123',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('events')->insert($events);
    }
}
