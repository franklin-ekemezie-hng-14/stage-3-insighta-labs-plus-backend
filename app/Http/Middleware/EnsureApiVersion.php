<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $version = $request->header('X-API-Version');

        if (! $version) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'API version header required',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($version !== '1') {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Invalid API version',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
