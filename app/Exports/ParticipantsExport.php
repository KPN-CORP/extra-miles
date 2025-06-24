<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\EventParticipant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
        // Ambil schema dulu untuk mapping question_1 → label
        $event = Event::where('id', $this->eventId)->select(['form_schema'])->first();

        $fieldMap = []; // key => label
        if ($event && $event->form_schema) {
            $schema = json_decode($event->form_schema, true);
            if (isset($schema['fields']) && is_array($schema['fields'])) {
                foreach ($schema['fields'] as $field) {
                    if (isset($field['name']) && isset($field['label'])) {
                        $fieldMap[$field['name']] = $field['label'];
                    }
                }
            }
        }

        Log::info('Field Map:', $fieldMap); // Untuk debug

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
                'attending_at',
                'form_data'
            ])
            ->get();

        return $participants->map(function ($item, $index) use ($fieldMap) {
            $base = [
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

            $formData = json_decode($item->form_data, true);

            foreach ($fieldMap as $key => $label) {
                $base[$label] = $formData[$key] ?? null;
            }

            return $base;
        });
    }

    public function headings(): array
    {
        $event = Event::where('id', $this->eventId)->select(['form_schema'])->first();

        $formLabels = [];

        if ($event && $event->form_schema) {
            $schema = json_decode($event->form_schema, true);
            if (isset($schema['fields']) && is_array($schema['fields'])) {
                foreach ($schema['fields'] as $field) {
                    $formLabels[] = $field['label'] ?? 'Unnamed Field';
                }
            }
        }

        return array_merge([
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
        ], $formLabels);
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
