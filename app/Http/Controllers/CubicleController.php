<?php

namespace App\Http\Controllers;

use App\Models\Cubicle;
use App\Services\CubicleService;
use App\Traits\ApiResponse;

class CubicleController extends Controller
{
    use ApiResponse;

    protected $cubicleService;

    public function __construct(CubicleService $cubicleService)
    {
        $this->cubicleService = $cubicleService;
    }

    /**
     * Obtiene la lista de cubículos
     */
    public function index()
    {
        $cubicles = $this->cubicleService->getAllCubicles();
        return $this->successResponse($cubicles);
    }

    /**
     * Muestra los detalles de un cubículo específico
     */
    public function show(Cubicle $cubicle)
    {
        return $this->successResponse($cubicle);
    }
}