<?php

namespace App\Http\Controllers;

use App\Models\ProcedureStandard;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProcedureStandardController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = ProcedureStandard::where('active', true);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ILIKE', "%{$search}%")
                  ->orWhere('standard', 'ILIKE', "%{$search}%")
                  ->orWhere('category', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $procedures = $query->orderBy('standard')->get();

        return $this->successResponse($procedures);
    }

    public function show(ProcedureStandard $procedureStandard)
    {
        return $this->successResponse($procedureStandard);
    }
}