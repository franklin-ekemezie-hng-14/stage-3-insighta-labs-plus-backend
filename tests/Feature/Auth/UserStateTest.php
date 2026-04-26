<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    $this->inactiveUser = User::create([
        'id' => Str::uuid(),
        'github_id' => 'inactive',
        'username' => 'inactiveuser',
        'email' => 'inactive@example.com',
        'role' => 'analyst',
        'is_active' => false,
        'last_login_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $this->token = $this->inactiveUser->createToken('access', ['access'])->plainTextToken;
    $this->refreshToken = $this->inactiveUser->createToken('refresh', ['refresh'])->plainTextToken;
});

it('inactive user cannot access API routes', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/profiles', ['X-API-Version' => '1']);
        
    $response->assertStatus(403)
        ->assertJson(['status' => 'error']);
});

it('inactive user cannot refresh token', function () {
    $response = $this->postJson('/auth/refresh', ['refresh_token' => $this->refreshToken]);
    
    $response->assertStatus(403);
});

it('inactive user cannot login successfully', function () {
    Session::put('oauth_state', 'valid_state_123');
    
    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['access_token' => 'gh_token'], 200),
        'api.github.com/user' => Http::response(['id' => 'inactive', 'login' => 'inactiveuser'], 200)
    ]);
    
    $response = $this->getJson('/auth/github/callback?code=fakecode&state=valid_state_123');
    
    $response->assertStatus(403)
        ->assertJson(['status' => 'error']);
});

it('must return 403 Forbidden consistently', function () {
    $response1 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/profiles/search?q=test', ['X-API-Version' => '1']);
    $response1->assertStatus(403);
    
    $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/profiles', ['name' => 'test'], ['X-API-Version' => '1']);
    $response2->assertStatus(403);
});
