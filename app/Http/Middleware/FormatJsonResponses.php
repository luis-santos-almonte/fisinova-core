<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FormatJsonResponses
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && $response->getStatusCode() < 400) {
            $originalContent = $response->getData(true);

            if (
                is_array($originalContent) &&
                array_key_exists('data', $originalContent) &&
                array_key_exists('error', $originalContent)
            ) {
                return $response;
            }

            $standardContent = [
                'data' => $originalContent,
                'error' => null,
            ]; 

            $response->setData($standardContent);
        }

        return $response;
    }
}