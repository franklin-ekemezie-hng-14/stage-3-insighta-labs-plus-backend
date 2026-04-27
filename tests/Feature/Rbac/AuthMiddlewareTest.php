<?php

use App\Enums\Role;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;


it('unauthenticated request to ANY api route fails with 401', function () {
    $endpoints = [
        ['GET', '/api/profiles'],
        ['POST', '/api/profiles'],
        ['DELETE', '/api/profiles/1'],
        ['GET', '/api/profiles/export?format=csv']
    ];

    foreach ($endpoints as [$method, $endpoint]) {
        $response = $this->json($method, $endpoint, [], ['X-API-Version' => '1']);
        $response->assertStatus(401);
    }
});

it('invalid token fails with 401', function () {
    $response = $this->withHeader('Authorization', "Bearer invalid_garbage_token")
                     ->getJson('/api/profiles', ['X-API-Version' => '1']);

    $response->assertStatus(401);
});

it('expired token fails with 401', function () {

    Sanctum::actingAs(
        $user = User::factory()->create(['role' => Role::ANALYST]),
        Role::ANALYST->abilities()
    )->tokens()->first();

    [
        'access_token' => $token,
    ] = $user->issueTokens();


    $dbToken = PersonalAccessToken::findToken($token);
    $dbToken->created_at = now()->subMinutes(60);
    $dbToken->save();

    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);

    $response->assertStatus(401);
});

it('missing token fails with 401', function () {
    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);
    $response->assertStatus(401);
});
