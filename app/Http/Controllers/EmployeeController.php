<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Traits\ApiResponse;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $employees = Employee::where('active', true)
                            ->select(['id', 'firstname', 'lastname', 'position_id'])
                            ->get();
        
        return $this->successResponse($employees);
    }

    public function show(Employee $employee)
    {
        return $this->successResponse($employee);
    }
}