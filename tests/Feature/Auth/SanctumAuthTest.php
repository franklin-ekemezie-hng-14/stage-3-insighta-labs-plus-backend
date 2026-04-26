<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->user = User::create([
        'id' => Str::uuid(),
        'github_id' => 'authuser',
        'username' => 'authuser',
        'email' => 'auth@example.com',
        'role' => 'analyst',
        'is_active' => true,
        'last_login_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $this->token = $this->user->createToken('access', ['access'])->plainTextToken;
});

it('authenticated request with valid token passes', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/profiles', ['X-API-Version' => '1']);
        
    $response->assertStatus(200);
});

it('missing token fails with 401', function () {
    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);
        
    $response->assertStatus(401)
        ->assertJson(['status' => 'error']);
});

it('expired access token fails', function () {
    $dbToken = PersonalAccessToken::findToken($this->token);
    $dbToken->created_at = now()->subMinutes(4);
    $dbToken->save();
    
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/profiles', ['X-API-Version' => '1']);
        
    $response->assertStatus(401);
});

it('invalid token fails safely', function () {
    $response = $this->withHeader('Authorization', 'Bearer invalid_garbage_token')
        ->getJson('/api/profiles', ['X-API-Version' => '1']);
        
    $response->assertStatus(401);
});

it('token is required for all /api/* routes', function () {
    $this->getJson('/api/profiles/search', ['X-API-Version' => '1'])->assertStatus(401);
    $this->postJson('/api/profiles', ['name' => 'Test'], ['X-API-Version' => '1'])->assertStatus(401);
});
