<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function profile()
    {
        try {
            // Log untuk memeriksa token dan employee_id
            $payload = JWTAuth::parseToken()->getPayload();
            $employee_id = $payload->get('employee_id');
            Log::info('Token payload employee_id: ' . $employee_id);

            $employee = Employee::select('employee_id', 'fullname', 'gender', 'email', 'group_company', 'designation_name', 'job_level', 'company_name', 'office_area', 'employee_type', 'unit', 'personal_mobile_number', 'religion', 'marital_status', 'whatsapp_number')->where('employee_id', $employee_id)->first();

            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            return response()->json($employee);
        } catch (\Exception $e) {
            Log::error('Error getting employees: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
