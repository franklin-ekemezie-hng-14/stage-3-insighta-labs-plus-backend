<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class HTTPException extends Exception
{

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function make(string $message = "", int $code = 0, ?Throwable $previous = null): static
    {
        return new static($message, $code, $previous);
    }

    public function toResponse(): JsonResponse
    {

        return response()->json([
            'status'    => 'error',
            'message'   => $this->message,
        ], $this->code);
    }

}
