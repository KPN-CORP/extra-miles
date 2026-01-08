<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::select(
                'employee_id',
                'fullname',
                'contribution_level_code'
            )
            ->orderBy('employee_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $employees
        ]);
    }
}
