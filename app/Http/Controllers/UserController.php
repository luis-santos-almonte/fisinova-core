<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(IndexUserRequest $request)
    {
        $users = $this->userService->getAllUsers($request->validated());
        return $this->successResponse($users);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());
        return $this->successResponse($user, 201);
    }

    public function show(User $user)
    {
        $user = $this->userService->getUserById($user->id);
        return $this->successResponse($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user = $this->userService->updateUser($user->id, $request->validated());
        return $this->successResponse($user);
    }

    public function destroy(User $user)
    {
        $this->userService->deleteUser($user->id);
        return $this->successResponse(null, 204);
    }

    public function resetPassword(User $user)
    {
        $this->userService->resetPassword($user->id);
        return $this->successResponse(['message' => 'Contraseña reseteada exitosamente']);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->userService->changePassword($request->user()->id, $request->validated());
        return $this->successResponse(['message' => 'Contraseña cambiada exitosamente']);
    }

    public function checkPasswordReset(Request $request)
    {
        $needsReset = $this->userService->needsPasswordReset($request->user()->id);
        return $this->successResponse(['needs_reset' => $needsReset]);
    }

    public function availableEmployees()
    {
        $employees = $this->userService->getAvailableEmployees();
        return $this->successResponse($employees);
    }
}