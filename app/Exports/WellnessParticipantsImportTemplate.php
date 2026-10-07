<?php

namespace App\Exports;

use App\Imports\WellnessParticipantsImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Blank sheet for the participant import. The header stays in English on
 * purpose: it is the column key the import looks for, not display text.
 */
class WellnessParticipantsImportTemplate implements FromArray, ShouldAutoSize, WithStyles
{
    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['Employee ID'],
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
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ab2f2b'],
                ],
            ],
            // Text, so IDs with leading zeros survive being typed into the sheet.
            'A2:A'.(WellnessParticipantsImport::MAX_ROWS + 1) => [
                'numberFormat' => ['formatCode' => NumberFormat::FORMAT_TEXT],
            ],
        ];
    }
}
