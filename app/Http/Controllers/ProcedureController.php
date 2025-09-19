<?php

namespace App\Http\Controllers;

use App\Http\Requests\Procedure\IndexProcedureRequest;
use App\Http\Requests\Procedure\StoreProcedureRequest; 
use App\Http\Requests\Procedure\UpdateProcedureRequest;
use App\Models\Procedure;
use App\Services\ProcedureService;
use App\Traits\ApiResponse;

class ProcedureController extends Controller
{
    use ApiResponse;

    protected $procedureService;

    public function __construct(ProcedureService $procedureService)
    {
        $this->procedureService = $procedureService;
    }

    public function index(IndexProcedureRequest $request)
    {
        $procedures = $this->procedureService->getAllProcedures($request->validated());
        return $this->successResponse($procedures);
    }

    public function store(StoreProcedureRequest $request)
    {
        $procedure = $this->procedureService->createProcedure($request->validated());
        return $this->successResponse($procedure, 201);
    }

    public function show(Procedure $procedure)
    {
        $procedure = $this->procedureService->getProcedureById($procedure->id);
        return $this->successResponse($procedure);
    }

    public function update(UpdateProcedureRequest $request, Procedure $procedure)
    {
        $procedure = $this->procedureService->updateProcedure($procedure->id, $request->validated());
        return $this->successResponse($procedure);
    }

    public function destroy(Procedure $procedure)
    {
        $this->procedureService->deleteProcedure($procedure->id);
        return $this->successResponse(null, 204);
    }
}