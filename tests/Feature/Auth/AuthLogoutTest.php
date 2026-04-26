<?php

use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::create([
        'id' => Str::uuid(),
        'github_id' => 'logout',
        'username' => 'logoutuser',
        'email' => 'logout@example.com',
        'role' => 'analyst',
        'is_active' => true,
        'last_login_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $this->accessToken = $this->user->createToken('access', ['access'])->plainTextToken;
    $this->refreshToken = $this->user->createToken('refresh', ['refresh'])->plainTextToken;
});

it('valid logout invalidates refresh token', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
        ->postJson('/auth/logout');
        
    $response->assertStatus(200);
    
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('refresh after logout must fail', function () {
    $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
        ->postJson('/auth/logout');
        
    $response = $this->postJson('/auth/refresh', ['refresh_token' => $this->refreshToken]);
    $response->assertStatus(401);
});

it('logout with invalid token returns error safely', function () {
    $response = $this->withHeader('Authorization', 'Bearer invalid_token')
        ->postJson('/auth/logout');
        
    $response->assertStatus(401);
});

it('logout is idempotent (multiple calls do not break system)', function () {
    $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
        ->postJson('/auth/logout')
        ->assertStatus(200);
        
    $this->withHeader('Authorization', 'Bearer ' . $this->accessToken)
        ->postJson('/auth/logout')
        ->assertStatus(401);
});
