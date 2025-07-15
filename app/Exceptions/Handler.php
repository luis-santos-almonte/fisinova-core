<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponse;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        NotFoundHttpException::class,
    ];

    public function register(): void
    {
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse('Datos inválidos', 'VALIDATION_ERROR', 422, $e->errors());
            }
            return parent::render($request, $e);
        });

        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse('Usuario no autenticado', 'UNAUTHENTICATED', 401);
            }
            return parent::render($request, $e);
        });

        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse('No autorizado', 'UNAUTHORIZED', 403);
            }
            return parent::render($request, $e);
        });

        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse('Recurso no encontrado', 'NOT_FOUND', 404);
            }
            return parent::render($request, $e);
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse('Ruta no encontrada', 'ROUTE_NOT_FOUND', 404);
            }
            return parent::render($request, $e);
        });

        $this->renderable(function (ApiException $e, Request $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse($e->getMessage(), $e->getErrorCode(), $e->getCode(), $e->getDetails());
            }
            return parent::render($request, $e);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            Log::channel('api')->error('Server Error', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            if ($request->expectsJson()) {
                return $this->errorResponse(
                    config('app.debug') ? $e->getMessage() : 'Error del servidor',
                    'SERVER_ERROR',
                    500,
                    config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null
                );
            }
            return parent::render($request, $e);
        });
    }
}