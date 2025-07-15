<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data, int $status = 200)
    {
        return response()->json([
            'data' => $data,
            'error' => null,
        ], $status);
    }

    protected function errorResponse(string $message, string $code, int $status, ?array $details = null)
    {
        return response()->json([
            'data' => null,
            'error' => [
                'message' => $message,
                'code' => $code,
                'details' => $details,
            ],
        ], $status);
    }
}
