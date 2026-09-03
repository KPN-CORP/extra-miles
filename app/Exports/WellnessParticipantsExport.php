<?php

namespace App\Exports;

use App\Models\WellnessActivityRegistration;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WellnessParticipantsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(protected int $scheduleId) {}

    public function collection(): Collection
    {
        $registrations = WellnessActivityRegistration::where('wellness_activity_schedule_id', $this->scheduleId)
            ->with(['statusHistories', 'activity'])
            ->orderBy('status')
            ->orderBy('registered_at')
            ->get();

        return $registrations->values()->map(function (WellnessActivityRegistration $item, int $index) {
            $latest = $item->statusHistories->last();

            return [
                'No' => $index + 1,
                'Employee ID' => $item->employee_id,
                'Full Name' => $item->fullname,
                'Business Unit' => $item->business_unit,
                'Unit' => $item->unit,
                'Job Level' => $item->job_level,
                'Location' => $item->location,
                'Method' => $item->activity?->registration_method?->shortLabel(),
                'Status' => $item->status->label(),
                'Registered Via' => $item->source->label(),
                'Registered At' => $item->registered_at?->format('Y-m-d H:i'),
                'Attended At' => $item->attended_at?->format('Y-m-d H:i'),
                'Attended' => $item->attended_at ? 'Yes' : 'No',
                'Last Remark' => $latest?->remark,
            ];
        });
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Employee ID',
            'Full Name',
            'Business Unit',
            'Unit',
            'Job Level',
            'Location',
            'Method',
            'Status',
            'Registered Via',
            'Registered At',
            'Attended At',
            'Attended',
            'Last Remark',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ab2f2b'],
                ],
            ],
        ];
    }
}
