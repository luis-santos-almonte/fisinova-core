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
        $query = ProcedureStandard::query()->where('active', true);

        // 🔍 Búsqueda general o específica
        if ($search = $request->get('search')) {
            $field = $request->get('field');

            if ($field && in_array($field, ['standard', 'description', 'category'])) {
                // Búsqueda en un campo específico
                $query->where($field, 'ILIKE', "%{$search}%");
            } else {
                // Búsqueda general (en todos)
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'ILIKE', "%{$search}%")
                        ->orWhere('standard', 'ILIKE', "%{$search}%")
                        ->orWhere('category', 'ILIKE', "%{$search}%");
                });
            }
        }

        // 🎯 Filtro por categoría
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // ⚡ Puedes limitar columnas si solo necesitas algunas
        $procedures = $query
            ->select('id', 'standard', 'description', 'category')
            ->orderBy('standard')
            ->get();

        return $this->successResponse($procedures);
    }
    public function show(ProcedureStandard $procedureStandard)
    {
        return $this->successResponse($procedureStandard);
    }
}
