<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Requests\FilterEmployeeRequest;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FilterEmployeeRequest $request)
    {
        $query = Employee::query()->active();

        if ($request->has('active')) {
            $query->where('active', $request->active);
        }

        if ($request->has('paginate')) {
            return response()->json($query->paginate($request->paginate));
        }

        return response()->json($query);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());

        return response()->json($employee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return response()->json($employee);
    }

    public function employeeWithRole()
    {
        $employees = Employee::with('role')->active()->get();

        return response()->json($employees);
    }
}
