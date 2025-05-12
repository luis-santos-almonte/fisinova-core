<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Support\Facades\Log;

class ApiExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public static function register(Exceptions $exceptions): void
    {


        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => 'Datos inválidos',
                        'details' => $e->errors(),
                    ],
                ], 422);
            }
            return null;
        });

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => 'Usuario no autenticado',
                    ],
                ], 401);
            }
            return null;
        });

        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => 'Recurso no encontrado',
                    ],
                ], 404);
            }
            return null;
        });

        $exceptions->renderable(function (HttpExceptionInterface $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => $e->getMessage() ?: 'Error HTTP',
                    ],
                ], $e->getStatusCode());
            }
            return null;
        });

        $exceptions->renderable(function (Throwable $e, Request $request) {
            if (config('app.debug')) {
                Log::error('Error 500 - ' . $e->getMessage(), [
                    'exception' => $e,
                    'request' => $request->all(),
                ]);
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => 'Error del servidor',
                    ],
                ], 500);
            }
            return null;
        });
    }
}
