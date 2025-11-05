<?php

namespace App\Http\Controllers;

use App\Http\Requests\Insurance\IndexInsuranceRequest;
use App\Http\Requests\Insurance\StoreInsuranceRequest;
use App\Http\Requests\Insurance\UpdateInsuranceRequest;
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

    /**
     * Obtiene la lista de seguros con filtros
     * 
     * @param IndexInsuranceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexInsuranceRequest $request)
    {
        $insurances = $this->insuranceService->getAllInsurance($request->validated());
        return $this->successResponse($insurances);
    }

    /**
     * Crea un nuevo seguro
     * 
     * @param StoreInsuranceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreInsuranceRequest $request)
    {
        $insurance = $this->insuranceService->createInsurance($request->validated());
        return $this->successResponse($insurance, 201);
    }

    /**
     * Muestra los detalles de un seguro específico
     * 
     * @param Insurance $insurance
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Insurance $insurance)
    {
        $insurance = $this->insuranceService->getInsuranceById($insurance->id);
        return $this->successResponse($insurance);
    }

    /**
     * Actualiza un seguro existente
     * 
     * @param UpdateInsuranceRequest $request
     * @param Insurance $insurance
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateInsuranceRequest $request, Insurance $insurance)
    {
        $insurance = $this->insuranceService->updateInsurance($insurance->id, $request->validated());
        return $this->successResponse($insurance);
    }

    /**
     * Elimina un seguro (soft delete)
     * 
     * @param Insurance $insurance
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Insurance $insurance)
    {
        $this->insuranceService->deleteInsurance($insurance->id);
        return $this->successResponse(null, 204);
    }

    /**
     * Activa o desactiva un seguro
     * 
     * @param Insurance $insurance
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Insurance $insurance)
    {
        $insurance = $this->insuranceService->toggleActive($insurance->id);
        return $this->successResponse($insurance);
    }

    /**
     * Obtiene estadísticas del seguro
     * 
     * @param Insurance $insurance
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Insurance $insurance)
    {
        $stats = $this->insuranceService->getInsuranceStatistics($insurance->id);
        return $this->successResponse($stats);
    }
}