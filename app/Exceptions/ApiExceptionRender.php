<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Auth\AuthenticationException;

class ApiExceptionRenderer
{
    protected Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function render(Request $request, Throwable $e): JsonResponse
    {
        $statusCode = $this->getStatusCode($e);

        $response = [
            'success' => false,
            'status' => $statusCode,
            'error' => [
                'message' => $this->getMessage($e),
                'details' => $this->getDetails($e)
            ]
        ];

        if (config('app.debug')) {
            $response['error']['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ];
        }

        return response()->json($response, $statusCode);
    }

    protected function getStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof ValidationException => 422,
            $e instanceof ModelNotFoundException => 404,
            $e instanceof AuthenticationException => 401,
            $e instanceof HttpException => $e->getStatusCode(),
            default => 500
        };
    }

    protected function getMessage(Throwable $e): string
    {
        return match (true) {
            $e instanceof ValidationException => 'Error de validación',
            $e instanceof ModelNotFoundException => 'Recurso no encontrado',
            $e instanceof AuthenticationException => 'No autenticado',
            $e instanceof HttpException => $e->getMessage() ?: 'Error HTTP',
            default => $e->getMessage() ?: 'Error del servidor'
        };
    }

    protected function getDetails(Throwable $e): mixed
    {
        return match (true) {
            $e instanceof ValidationException => $e->errors(),
            default => null
        };
    }
}
