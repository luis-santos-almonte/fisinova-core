<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        // Add exceptions to exclude from logging, if needed
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => 'Datos inválidos',
                        'details' => $e->errors(),
                        'code' => 'VALIDATION_ERROR',
                    ],
                ], 422);
            }
        });

        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => 'Usuario no autenticado',
                        'code' => 'UNAUTHENTICATED',
                    ],
                ], 401);
            }
        });

        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => 'No autorizado',
                        'code' => 'UNAUTHORIZED',
                    ],
                ], 403);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => 'Recurso no encontrado',
                        'code' => 'NOT_FOUND',
                    ],
                ], 404);
            }
        });

        $this->renderable(function (HttpExceptionInterface $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => $e->getMessage() ?: 'Error HTTP',
                        'code' => 'HTTP_ERROR',
                    ],
                ], $e->getStatusCode());
            }
        });

        $this->renderable(function (Throwable $e, Request $request) {
            Log::error('Server Error', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'data' => null,
                    'error' => [
                        'message' => config('app.debug') ? $e->getMessage() : 'Error del servidor',
                        'code' => 'SERVER_ERROR',
                        'details' => config('app.debug') ? [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ] : null,
                    ],
                ], 500);
            }
        });
    }
}