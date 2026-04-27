<?php

use App\Exceptions\HTTPException;
use App\Http\Middleware\EnsureApiVersion;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\LogRequest;
use App\Models\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //

        $middleware->trustProxies(at: '*');

        $middleware->append([
            EnsureApiVersion::class,
            LogRequest::class
        ]);

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //


        $exceptions->render(function (NotFoundHttpException $e) {

            $previousException = $e->getPrevious();

            if ($previousException instanceof ModelNotFoundException) {

                $message = match ($previousException->getModel()) {
                    Profile::class  => 'Profile not found',
                    default         => null
                };

                if ($message) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => $message,
                    ], Response::HTTP_NOT_FOUND);
                }

            }


            return null;
        });

        $exceptions->render(fn (HTTPException $e) => $e->toResponse());

        $exceptions->render(function (ValidationException $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Invalid query parameters',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(fn (InvalidStateException $e) => response()->json([
            'status'    => 'error',
            'message'   => 'Invalid state'
        ], Response::HTTP_BAD_REQUEST));

        $exceptions->render(fn (ThrottleRequestsException $e) => response()->json([
            'status'    => 'error',
            'message'   => 'Too many requests',
        ], Response::HTTP_TOO_MANY_REQUESTS));

        if (app()->environment() === 'production') {
            $exceptions->render(fn (Throwable $e) => response()->json([
                'status'    => 'error',
                'message'   => 'Something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR));
        }



    })->create();
