<?php

use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'admin_id_ver',
        'username' => 'admin_user_ver',
        'email' => 'adminver@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    $this->token = $this->admin->createToken('access')->plainTextToken;
});

it('rejects export if X-API-Version header is missing', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/export?format=csv'); // Missing header

    $response->assertStatus(400)
             ->assertJson([
                 'status' => 'error',
                 'message' => 'API version header required'
             ]);
});

it('rejects export if X-API-Version header is invalid', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '2']); // Invalid header

    // Expecting 400 bad request for incorrect version or valid fallback error
    expect(in_array($response->status(), [400, 422]))->toBeTrue();
});

it('executes normally if X-API-Version is exactly 1', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $response->assertStatus(200);
});
