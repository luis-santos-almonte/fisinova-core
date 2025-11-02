<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Traits\ApiResponse;

class RoleController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $roles = Role::where('active', true)
            ->orderBy('name')
            ->get();
            
        return $this->successResponse($roles);
    }
}