<?php

namespace App\Http\Controllers;

use App\Http\Requests\Insurance\IndexInsuranceRequest;
use App\Models\Insurance;
use App\Services\InsuranceService;
use App\Traits\ApiResponse;

class InsuranceController extends Controller
{
    use ApiResponse;

    protected $insuranceService;

    public function __construct(InsuranceService $insuranceService)
    {
        $this->insuranceService = $insuranceService;
    }

    public function index(IndexInsuranceRequest $request)
    {
        $insurances = $this->insuranceService->getAllInsurance($request->validated());
        return $this->successResponse($insurances);
    }

    public function show(Insurance $insurance)
    {
        $insurance = $this->insuranceService->getInsuranceById($insurance->id);
        return $this->successResponse($insurance);
    }

    public function destroy(Insurance $insurance)
    {
        $this->insuranceService->deleteInsurance($insurance->id);
        return $this->successResponse(null, 204);
    }
}
