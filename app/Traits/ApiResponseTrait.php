<?php

namespace App\Traits;

trait ApiResponseTrait
{

    public function successResponse(string $message = null, $data = [],  int $statusCode = 200) 
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public function respondServerError(string $message = null,   int $statusCode = 500,)
    {
        return response()->json([
            'message' => $message,
        ], $statusCode);
    }
}
