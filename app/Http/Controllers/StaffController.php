<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Http\Requests\Staff\IndexStaffRequest;
use App\Models\Staff;
use App\Services\StaffService;
use App\Traits\ApiResponse;

class StaffController extends Controller
{
    use ApiResponse;

    protected $staffService;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function index(IndexStaffRequest $request)
    {
        $staff = $this->staffService->getAllStaff($request->validated());
        return $this->successResponse($staff);
    }

    public function store(StoreStaffRequest $request)
    {
        $staff = $this->staffService->createStaff($request->validated());
        return $this->successResponse($staff, 201);
    }

    public function show(Staff $staff)
    {
        $staff = $this->staffService->getStaffById($staff->id);
        return $this->successResponse($staff);
    }

    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        $staff = $this->staffService->updateStaff($staff->id, $request->validated());
        return $this->successResponse($staff);
    }

    public function destroy(Staff $staff)
    {
        $this->staffService->deleteStaff($staff->id);
        return $this->successResponse(null, 204);
    }
}