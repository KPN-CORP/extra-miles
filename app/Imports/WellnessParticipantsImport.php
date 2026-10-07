<?php

namespace App\Imports;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reads the employee IDs out of an uploaded participant sheet. Only the IDs are
 * taken from the file -- name, unit and the rest of the HR snapshot are always
 * looked up on `kpncorp`, so a stale or hand-edited sheet cannot put wrong data
 * on a registration.
 */
class WellnessParticipantsImport implements WithHeadingRow
{
    /**
     * Header slugs accepted for the ID column. The template says "Employee ID";
     * the others cover sheets exported from HR or written in Indonesian.
     */
    public const ID_COLUMNS = ['employee_id', 'id_karyawan', 'nik', 'employee'];

    public const MAX_ROWS = 500;

    /**
     * @return Collection<int, array{row: int, employee_id: string}> sheet row number => trimmed ID, blanks dropped
     */
    public static function employeeIds(UploadedFile $file): Collection
    {
        $rows = Excel::toCollection(new self, $file)->first() ?? collect();

        return $rows
            ->map(function ($row, int $index) {
                $row = collect($row);
                $column = collect(self::ID_COLUMNS)->first(fn ($key) => $row->has($key));
                $value = $column ? $row->get($column) : $row->first();

                return [
                    // +2: zero-based index, plus the heading row.
                    'row' => $index + 2,
                    'employee_id' => self::normalise($value),
                ];
            })
            ->filter(fn ($item) => $item['employee_id'] !== '')
            ->values();
    }

    /**
     * Spreadsheets hand numeric IDs back as int/float (12345.0), so turn them
     * back into the string the HR database stores.
     */
    protected static function normalise(mixed $value): string
    {
        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }

        return trim((string) $value);
    }
}
