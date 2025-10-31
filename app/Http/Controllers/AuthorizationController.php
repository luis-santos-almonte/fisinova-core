<?php
// app/Http/Controllers/AuthorizationController.php

namespace App\Http\Controllers;

use App\Http\Requests\Authorization\IndexAuthorizationRequest;
use App\Http\Requests\Authorization\StoreAuthorizationRequest;
use App\Http\Requests\Authorization\UpdateAuthorizationRequest;
use App\Http\Requests\Authorization\ConfirmAppointmentRequest;
use App\Models\Authorization;
use App\Services\AuthorizationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
    use ApiResponse;

    protected $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    public function index(IndexAuthorizationRequest $request)
    {
        $authorizations = $this->authorizationService->getAllAuthorizations($request->validated());
        return $this->successResponse($authorizations);
    }

    public function store(StoreAuthorizationRequest $request)
    {
        $authorization = $this->authorizationService->createAuthorization($request->validated());
        return $this->successResponse($authorization, 201);
    }

    public function show(Authorization $authorization)
    {
        $authorization = $this->authorizationService->getAuthorizationById($authorization->id);
        return $this->successResponse($authorization);
    }

    public function update(UpdateAuthorizationRequest $request, Authorization $authorization)
    {
        $authorization = $this->authorizationService->updateAuthorization(
            $authorization->id,
            $request->validated()
        );
        return $this->successResponse($authorization);
    }

    public function destroy(Authorization $authorization)
    {
        $this->authorizationService->deleteAuthorization($authorization->id);
        return $this->successResponse(null, 204);
    }

    public function confirmAppointment(ConfirmAppointmentRequest $request, $appointmentId)
    {
        $appointment = $this->authorizationService->confirmAppointment(
            $appointmentId,
            $request->validated(),
            $request->user()->id
        );
        return $this->successResponse($appointment);
    }
}

