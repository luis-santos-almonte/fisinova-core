<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class ApiResponseFormatter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }

        $originalData = $response->getData(true);

        $isSuccess = $response->getStatusCode() < 400;

        $formatted = [
            'success' => $isSuccess,
            'status' => $response->getStatusCode(),
            'data' => $isSuccess ? $originalData : null,
            'error' => !$isSuccess ? $originalData : null,
        ];

        return response()->json($formatted, $response->getStatusCode());
    }
}
