<?php

namespace App\Http\Middleware;

use App\Exceptions\HTTPException;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws HTTPException
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = $request->user();

        if (! $user || ! $user->isActive()) {
            throw new HTTPException('User is not active.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
