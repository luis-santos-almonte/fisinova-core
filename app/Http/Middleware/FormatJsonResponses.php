<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

class FormatJsonResponses
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($request->expectsJson() && $response instanceof JsonResponse && $response->getStatusCode() < 400) {
            $originalContent = $response->getData(true);

            if (
                is_array($originalContent) &&
                array_key_exists('data', $originalContent) &&
                array_key_exists('error', $originalContent)
            ) {
                return $response;
            }

            return $this->successResponse($originalContent, $response->getStatusCode());
        }

        return $response;
    }
}
