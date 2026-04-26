<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->user = User::create([
        'id' => Str::uuid(),
        'github_id' => '123',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'role' => 'analyst',
        'is_active' => true,
        'last_login_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $this->token = $this->user->createToken('refresh', ['refresh'])->plainTextToken;
});

it('valid refresh token returns new token pair', function () {
    $response = $this->postJson('/auth/refresh', [
        'refresh_token' => $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'access_token',
            'refresh_token'
        ])
        ->assertJson(['status' => 'success']);
        
    expect($response->json('refresh_token'))->not->toBe($this->token);
});

it('old refresh token becomes invalid immediately', function () {
    $this->postJson('/auth/refresh', ['refresh_token' => $this->token]);
    
    $response = $this->postJson('/auth/refresh', ['refresh_token' => $this->token]);
    
    $response->assertStatus(401);
});

it('reused refresh token is rejected', function () {
    $response1 = $this->postJson('/auth/refresh', ['refresh_token' => $this->token]);
    $newToken = $response1->json('refresh_token');
    
    $response2 = $this->postJson('/auth/refresh', ['refresh_token' => $this->token]);
    $response2->assertStatus(401);
});

it('expired refresh token is rejected', function () {
    $dbToken = PersonalAccessToken::findToken($this->token);
    $dbToken->created_at = now()->subMinutes(6);
    $dbToken->save();
    
    $response = $this->postJson('/auth/refresh', ['refresh_token' => $this->token]);
    
    $response->assertStatus(401)
        ->assertJson(['status' => 'error']);
});

it('malformed token returns error', function () {
    $response = $this->postJson('/auth/refresh', ['refresh_token' => 'invalid_format_token']);
    
    $response->assertStatus(401);
});

it('response format matches TRD exactly', function () {
    $response = $this->postJson('/auth/refresh', ['refresh_token' => $this->token]);

    $response->assertExactJson([
        'status' => 'success',
        'access_token' => $response->json('access_token'),
        'refresh_token' => $response->json('refresh_token')
    ]);
});
