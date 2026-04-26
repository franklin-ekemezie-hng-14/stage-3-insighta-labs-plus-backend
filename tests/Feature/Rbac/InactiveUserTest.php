<?php

use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->inactiveAdmin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'inactive_admin',
        'username' => 'off_user',
        'email' => 'off@insighta.local',
        'role' => 'admin',
        'is_active' => false,
    ]);

    $this->token = $this->inactiveAdmin->createToken('access')->plainTextToken;
});

it('inactive user cannot access ANY endpoint', function () {
    $endpoints = [
        ['GET', '/api/profiles'],
        ['POST', '/api/profiles', ['name' => 'Test']],
        ['DELETE', '/api/profiles/1'],
        ['GET', '/api/profiles/export?format=csv']
    ];

    foreach ($endpoints as $e) {
        $method = $e[0];
        $endpoint = $e[1];
        $payload = $e[2] ?? [];
        
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->json($method, $endpoint, $payload, ['X-API-Version' => '1']);
                         
        $response->assertStatus(403)
                 ->assertJson(['status' => 'error']);
    }
});

it('always returns 403 even with valid token', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles', ['X-API-Version' => '1']);
                     
    $response->assertStatus(403);
});
