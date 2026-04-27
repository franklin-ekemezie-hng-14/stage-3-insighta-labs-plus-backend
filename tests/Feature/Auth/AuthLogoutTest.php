<?php

use App\Enums\Role;
use App\Models\RefreshToken;
use App\Models\User;
use Laravel\Sanctum\Sanctum;


function fakeRefreshToken(): string {
    $user = Sanctum::actingAs(
        User::factory()->create(),
        Role::ANALYST->abilities()
    );

    $refreshToken = RefreshToken::query()->make([
        'token' => Hash::make(Str::random(40)),
    ]);
    $refreshToken->setAttribute('expires_at', now()->addMinutes(10));
    $refreshToken->user()->associate($user);
    $refreshToken->save();

    return $refreshToken->token;
}

it('valid logout invalidates refresh token', function () {

    $response = $this
        ->withHeader('Authorization', 'Bearer ' . fakeRefreshToken())
        ->postJson('/auth/logout');

    $response->assertStatus(200);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('refresh after logout must fail', function () {

    $this
        ->withHeader('Authorization', 'Bearer ' . $refreshToken  = fakeRefreshToken())
        ->postJson('/auth/logout');

    $response = $this->postJson('/auth/refresh', ['refresh_token' => $refreshToken]);
    $response->assertStatus(401);
});

it('logout with invalid token returns error safely', function () {
    $response = $this
        ->withHeader('Authorization', 'Bearer ' . 'fake-token')
        ->postJson('/auth/logout');

    $response->assertStatus(401);
});

it('logout is idempotent (multiple calls do not break system)', function () {
    $this
        ->withHeader('Authorization', 'Bearer ' . $accessToken = fakeRefreshToken())
        ->postJson('/auth/logout')
        ->assertStatus(200);

    $this->withHeader('Authorization', 'Bearer ' . $accessToken)
        ->postJson('/auth/logout')
        ->assertStatus(401);
});
