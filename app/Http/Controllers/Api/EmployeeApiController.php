<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::select(
            'employee_id',
            'fullname',
            'contribution_level_code'
        );

        // optional filter
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderBy('employee_id')->get()
        ]);
    }
}
