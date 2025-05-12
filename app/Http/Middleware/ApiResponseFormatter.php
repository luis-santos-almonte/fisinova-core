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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $originalContent = $response->getData(true);

            if (
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
