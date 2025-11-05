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
        $query = DiagnosticStandard::query()
            ->where('active', true);

        // 🔍 Búsqueda general o específica
        if ($search = $request->get('search')) {
            $field = $request->get('field');

            if ($field && in_array($field, ['code', 'description', 'category'])) {
                // Búsqueda específica
                $query->where($field, 'ILIKE', "%{$search}%");
            } else {
                // Búsqueda general (por varios campos)
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'ILIKE', "%{$search}%")
                        ->orWhere('description', 'ILIKE', "%{$search}%")
                        ->orWhere('category', 'ILIKE', "%{$search}%");
                });
            }
        }

        // 🧩 Filtros adicionales
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if (!is_null($request->get('chronic'))) {
            $query->where('chronic', filter_var($request->get('chronic'), FILTER_VALIDATE_BOOLEAN));
        }

        // ⚡ Resultado
        $diagnostics = $query
            ->select('id', 'code', 'description', 'category', 'chronic')
            ->orderBy('code')
            ->paginate(25);

        return $this->successResponse($diagnostics);
    }

    public function show(DiagnosticStandard $diagnosticStandard)
    {
        return $this->successResponse($diagnosticStandard);
    }
}
