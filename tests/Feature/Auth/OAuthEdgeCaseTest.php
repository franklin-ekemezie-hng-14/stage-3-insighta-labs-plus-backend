<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

it('repeated OAuth callback attempt fails gracefully', function () {
    Session::put('oauth_state', 'valid_123');
    
    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['access_token' => 'token'], 200),
        'api.github.com/user' => Http::response(['id' => 111, 'login' => 'user1', 'email' => 'u1@example.com'], 200)
    ]);

    $this->getJson('/auth/github/callback?code=code&state=valid_123')->assertStatus(200);
    
    $this->getJson('/auth/github/callback?code=code&state=valid_123')->assertStatus(400);
});

it('race condition: double refresh request throws 401 for second request', function () {
    $user = User::create([
        'id' => Str::uuid(),
        'github_id' => 'race',
        'username' => 'raceuser',
        'email' => 'race@example.com',
        'role' => 'analyst',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    $token = $user->createToken('refresh', ['refresh'])->plainTextToken;
    
    $response1 = $this->postJson('/auth/refresh', ['refresh_token' => $token]);
    $response1->assertStatus(200);
    
    $response2 = $this->postJson('/auth/refresh', ['refresh_token' => $token]);
    $response2->assertStatus(401);
});

it('malformed refresh token payload returns error safely', function () {
    $this->postJson('/auth/refresh', ['invalid_key' => 'token'])->assertStatus(401);
});

it('missing state in callback returns 400', function () {
    $this->getJson('/auth/github/callback?code=code')->assertStatus(400);
});

it('GitHub API failure is handled safely', function () {
    Session::put('oauth_state', 'valid_123');
    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(null, 500)
    ]);

    $this->getJson('/auth/github/callback?code=code&state=valid_123')
        ->assertStatus(502)
        ->assertJson(['status' => 'error']);
});

it('user creation failure rollback safety', function () {
    Session::put('oauth_state', 'valid_123');
    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['access_token' => 'token'], 200),
        'api.github.com/user' => Http::response(['id' => 111, 'login' => null], 200)
    ]);
    
    $response = $this->getJson('/auth/github/callback?code=code&state=valid_123');
    
    expect(in_array($response->status(), [500, 502, 422]))->toBeTrue();
    $response->assertJson(['status' => 'error']);
});

it('token reuse attack simulation', function () {
    $user = User::create([
        'id' => Str::uuid(),
        'github_id' => 'reuse',
        'username' => 'reuseuser',
        'email' => 'reuse@example.com',
        'role' => 'analyst',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $token = $user->createToken('refresh', ['refresh'])->plainTextToken;
    
    $this->postJson('/auth/refresh', ['refresh_token' => $token])->assertStatus(200);
    
    $this->postJson('/auth/refresh', ['refresh_token' => $token])->assertStatus(401);
});

it('expired session handling', function () {
    $this->getJson('/auth/github/callback?code=code&state=expired_state')->assertStatus(400);
});
