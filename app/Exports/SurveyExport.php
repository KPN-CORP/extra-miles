<?php

namespace App\Exports;

use App\Models\Survey;
use App\Models\SurveyParticipant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SurveyExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    protected $survey_id;

    public function __construct($survey_id)
    {
        $this->survey_id = $survey_id;
    }

    public function collection()
    {
        $survey = Survey::where('id', $this->survey_id)->select(['form_schema'])->first();

        $fieldMap = []; // key => label
        if ($survey && $survey->form_schema) {
            $schema = json_decode($survey->form_schema, true);
            if (isset($schema['fields']) && is_array($schema['fields'])) {
                foreach ($schema['fields'] as $field) {
                    if (isset($field['name']) && isset($field['label'])) {
                        $fieldMap[$field['name']] = $field['label'];
                    }
                }
            }
        }

        $participants = SurveyParticipant::where('survey_id', $this->survey_id)
            ->select([
                'employee_id',
                'fullname',
                'business_unit',
                'job_level',
                'location',
                'unit',
                'form_data',
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
        $survey = Survey::where('id', $this->survey_id)->select(['form_schema'])->first();

        $formLabels = [];

        if ($survey && $survey->form_schema) {
            $schema = json_decode($survey->form_schema, true);
            if (isset($schema['fields']) && is_array($schema['fields'])) {
                foreach ($schema['fields'] as $field) {
                    $formLabels[] = $field['label'] ?? __('Unnamed Field');
                }
            }
        }

        return array_merge([
            'No',
            __('Employee ID'),
            __('Full Name'),
            __('Business Unit'),
            __('Job Level'),
            __('Location'),
            __('Unit'),
        ], $formLabels);
    }

    public function styles(Worksheet $sheet)
    {
        // Set heading row (baris ke-1)
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ab2f2b'], // warna merah muda
                ],
            ],
        ];
    }
}
