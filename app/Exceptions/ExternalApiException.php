<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ExternalApiException extends HTTPException
{
    //

    public function __construct(
        protected string $apiName,
        string $message='',
        int $code = 0,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function toResponse(): JsonResponse
    {

        return response()->json([
            'status'    => 'error',
            'message'   => "$this->apiName returned an invalid response",
        ], Response::HTTP_BAD_GATEWAY);
    }
}
