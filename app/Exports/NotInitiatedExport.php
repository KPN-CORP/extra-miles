<?php

namespace App\Exports;

use App\Models\ApprovalLayer;
use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class NotInitiatedExport implements FromView, WithStyles
{
    use Exportable;

    protected $employeeId;

    public function __construct($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    public function view(): View
    {
        $user = $this->employeeId;

        if(Auth()->user()->isApprover()){

            $query = ApprovalLayer::with(['employee', 'subordinates'])
            ->leftJoin('employees', 'approval_layers.employee_id', '=', 'employees.employee_id')
            ->leftJoin('schedules', function($join) {
                $join->on('employees.employee_type', '=', 'schedules.employee_type')
                    ->whereRaw('FIND_IN_SET(employees.group_company, schedules.bisnis_unit)')
                    ->where(function($query) {
                        $query->whereRaw('(schedules.company_filter IS NULL OR schedules.company_filter = "")')
                            ->orWhereRaw('FIND_IN_SET(employees.company_name, schedules.company_filter)');
                    })
                    ->where(function($query) {
                        $query->whereRaw('(schedules.location_filter IS NULL OR schedules.location_filter = "")')
                            ->orWhereRaw('FIND_IN_SET(employees.work_area_code, schedules.location_filter)');
                    });
            })
            ->whereColumn('employees.date_of_joining', '<', 'schedules.last_join_date')
            ->whereNull('schedules.deleted_at')
            ->where('approval_layers.approver_id', $user)
            ->whereDoesntHave('subordinates', function ($query) use ($user) {
                $query->with([
                    'goal', 
                    'updatedBy', 
                    'approval' => function ($query) {
                        $query->with('approverName');
                    }
                ])->whereHas('approvalLayer', function ($query) use ($user) {
                    $query->where('employee_id', $user)->orWhere('approver_id', $user);
                });
            })
            ->select('approval_layers.*', 'employees.date_of_joining', 'schedules.last_join_date')
            ->distinct();
        
        }

        $data = $query->get();

        return view('exports.notInitiated', compact('data'));

    }

    public function styles($sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFFF00']]
            ],
        ];
    }
}
