<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class EmployeeDetailExport implements FromView
{
    // /** View
    public function view(): View
    {
        return view('exports.reportemp', [
            'employees' => Employee::all()
        ]);
    }
    
}
