<?php

namespace App\Http\Controllers;

use App\Models\DiagnosticStandard;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DiagnosticStandardController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = DiagnosticStandard::where('active', true);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ILIKE', "%{$search}%")
                    ->orWhere('code', 'ILIKE', "%{$search}%")
                    ->orWhere('category', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('chronic')) {
            $query->where('chronic', $request->chronic === 'true');
        }

        $diagnostics = $query->orderBy('code')->get();

        return $this->successResponse($diagnostics);
    }

    public function show(DiagnosticStandard $diagnosticStandard)
    {
        return $this->successResponse($diagnosticStandard);
    }
}
