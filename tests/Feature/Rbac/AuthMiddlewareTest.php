<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->user = User::create([
        'id' => Str::uuid(),
        'github_id' => 'auth_mid',
        'username' => 'auth_mid_user',
        'email' => 'auth@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    $this->token = $this->user->createToken('access')->plainTextToken;
});

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
    $dbToken = PersonalAccessToken::findToken($this->token);
    $dbToken->created_at = now()->subMinutes(60);
    $dbToken->save();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles', ['X-API-Version' => '1']);
                     
    $response->assertStatus(401);
});

it('missing token fails with 401', function () {
    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);
    $response->assertStatus(401);
});
