<?php
namespace App\Http\Controllers;

use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Traits\ApiResponse;

class EmployeeController extends Controller
{
    use ApiResponse;

    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(IndexEmployeeRequest $request)
    {
        $employees = $this->employeeService->getAllEmployees($request->validated());
        return $this->successResponse($employees);
    }

    public function show(Employee $employee)
    {
        $employee = $this->employeeService->getEmployeeById($employee->id);
        return $this->successResponse($employee);
    }
}