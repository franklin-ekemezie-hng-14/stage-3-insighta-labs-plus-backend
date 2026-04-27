<?php

namespace App\Http\Controllers;

use App\Models\RefreshToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function Pest\Laravel\post;

class AuthenticatedSessionController extends Controller
{
    //

    public function refresh(Request $request)
    {

        $refreshToken = $request->input('refresh_token');

        if (! $refreshToken) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Empty refresh token',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (! is_string($refreshToken)) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Invalid refresh token',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var RefreshToken|null $token */
        $token = RefreshToken::query()
            ->where('revoked', false)
            ->get()
            ->first(fn (RefreshToken $token) => $token->check($refreshToken));

        if (! $token || $token->isExpired()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid or expired refresh token');
        }

        // Rotate token: revoke current token and issue new one
        $token->revoke();
        [
            'refresh_token' => $refreshToken,
            'access_token'  => $accessToken,
        ] = $token->user->issueTokens();

        return response()->json([
            'status'        => 'success',
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
        ]);

    }

    public function logout(Request $request)
    {

        $refreshToken = $request->bearerToken();
        if (! $refreshToken) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Empty refresh token',
            ], Response::HTTP_BAD_REQUEST);
        }

        RefreshToken::all()
            ->first(fn (RefreshToken $token) => $token->check($refreshToken))
            ?->revoke();

        return response()->json([
            'status'        => 'success',
            'message'       => 'Logged out successfully',
        ]);
    }
}
