<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Services\PositionService;
use App\Traits\ApiResponse;

class PositionController extends Controller
{
    use ApiResponse;

    protected $positionService;

    public function __construct(PositionService $positionService)
    {
        $this->positionService = $positionService;
    }

    /**
     * Obtiene la lista de posiciones
     */
    public function index()
    {
        $positions = $this->positionService->getAllPositions();
        return $this->successResponse($positions);
    }

    /**
     * Muestra los detalles de una posición específica
     */
    public function show(Position $position)
    {
        return $this->successResponse($position);
    }
}