<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'admin_id_auth',
        'username' => 'admin_user',
        'email' => 'admin@insighta.local',
        'role' => 'admin',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->token = $this->admin->createToken('access')->plainTextToken;
});

it('rejects missing token with 401', function () {
    $response = $this->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
    
    $response->assertStatus(401)
             ->assertJson(['status' => 'error']);
});

it('rejects invalid token with 401', function () {
    $response = $this->withHeader('Authorization', 'Bearer invalid_garbage_token')
                     ->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $response->assertStatus(401);
});

it('rejects expired token with 401', function () {
    $dbToken = PersonalAccessToken::findToken($this->token);
    $dbToken->created_at = now()->subMinutes(60);
    $dbToken->save();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $response->assertStatus(401);
});

it('rejects revoked or missing token immediately', function () {
    $this->admin->tokens()->delete(); // Revoke tokens

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $response->assertStatus(401);
});
