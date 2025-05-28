<?php

namespace App\Exports;

use App\Models\EventParticipant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $eventId;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    public function collection()
    {
        $participants = EventParticipant::where('event_id', $this->eventId)
            ->select([
                'employee_id',
                'fullname',
                'business_unit',
                'job_level',
                'location',
                'unit',
                'status',
                'messages',
                'attending_status',
                'attending_at'
            ])
            ->get();

        // Tambahkan nomor urut manual
        return $participants->map(function ($item, $index) {
            return [
                'No' => $index + 1,
                'employee_id' => $item->employee_id,
                'fullname' => $item->fullname,
                'business_unit' => $item->business_unit,
                'job_level' => $item->job_level,
                'location' => $item->location,
                'unit' => $item->unit,
                'status' => $item->status,
                'messages' => $item->messages,
                'attending_status' => $item->attending_status,
                'attending_at' => $item->attending_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Employee ID',
            'Full Name',
            'Business Unit',
            'Job Level',
            'Location',
            'Unit',
            'Status',
            'Messages',
            'Attending Status',
            'Attending At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set heading row (baris ke-1)
        return [
            1 => [
                'font' => ['bold' => true,'color' => ['rgb' => 'FFFFFF'],],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ab2f2b'], // warna merah muda
                ],
            ],
        ];
    }
}
