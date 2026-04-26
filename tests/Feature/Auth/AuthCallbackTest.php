<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Session::put('oauth_state', 'valid_state_123');
    Session::put('oauth_code_verifier', 'valid_verifier');
});

it('successful OAuth login creates new user', function () {
    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['access_token' => 'gh_fake_token'], 200),
        'api.github.com/user' => Http::response([
            'id' => 12345,
            'login' => 'testuser',
            'email' => 'test@example.com',
            'avatar_url' => 'http://example.com/avatar.jpg'
        ], 200)
    ]);

    $response = $this->getJson('/auth/github/callback?code=fakecode&state=valid_state_123');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'access_token',
            'refresh_token'
        ])
        ->assertJsonFragment(['status' => 'success']);

    $this->assertDatabaseHas('users', [
        'github_id' => '12345',
        'username' => 'testuser',
        'role' => 'analyst'
    ]);
});

it('existing user is updated (not duplicated)', function () {
    User::create([
        'id' => Str::uuid(),
        'github_id' => '12345',
        'username' => 'old_name',
        'email' => 'old@example.com',
        'role' => 'admin',
        'is_active' => true,
        'last_login_at' => now()->subDay(),
        'created_at' => now(),
        'updated_at' => now()
    ]);

    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['access_token' => 'gh_fake_token'], 200),
        'api.github.com/user' => Http::response([
            'id' => 12345,
            'login' => 'new_name',
            'email' => 'new@example.com',
            'avatar_url' => 'http://example.com/avatar.jpg'
        ], 200)
    ]);

    $response = $this->getJson('/auth/github/callback?code=fakecode&state=valid_state_123');

    $response->assertStatus(200);

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('users', [
        'github_id' => '12345',
        'username' => 'new_name',
        'role' => 'admin'
    ]);
});

it('invalid state is rejected', function () {
    $response = $this->getJson('/auth/github/callback?code=fakecode&state=invalid_state');

    $response->assertStatus(400)
        ->assertJson([
            'status' => 'error',
            'message' => 'Invalid state'
        ]);
});

it('invalid code is rejected', function () {
    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['error' => 'bad_verification_code'], 400)
    ]);

    $response = $this->getJson('/auth/github/callback?code=invalidcode&state=valid_state_123');

    $response->assertStatus(400)
        ->assertJson(['status' => 'error']);
});

it('user is assigned default role = analyst', function () {
    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['access_token' => 'gh_fake_token'], 200),
        'api.github.com/user' => Http::response([
            'id' => 999,
            'login' => 'analystuser'
        ], 200)
    ]);

    $this->getJson('/auth/github/callback?code=fakecode&state=valid_state_123');

    $this->assertDatabaseHas('users', [
        'github_id' => '999',
        'role' => 'analyst'
    ]);
});

it('access_token and refresh_token are returned', function () {
    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['access_token' => 'token'], 200),
        'api.github.com/user' => Http::response(['id' => 111, 'login' => 'user111'], 200)
    ]);

    $response = $this->getJson('/auth/github/callback?code=fakecode&state=valid_state_123');

    $response->assertJsonStructure(['access_token', 'refresh_token']);
});

it('last_login_at is updated', function () {
    $user = User::create([
        'id' => Str::uuid(),
        'github_id' => '777',
        'username' => 'lastlogin',
        'email' => 'last@example.com',
        'role' => 'analyst',
        'is_active' => true,
        'last_login_at' => now()->subDays(5),
        'created_at' => now(),
        'updated_at' => now()
    ]);

    Http::fake([
        'github.com/login/oauth/access_token' => Http::response(['access_token' => 'token'], 200),
        'api.github.com/user' => Http::response(['id' => 777, 'login' => 'lastlogin'], 200)
    ]);

    $this->getJson('/auth/github/callback?code=fakecode&state=valid_state_123');

    $user->refresh();
    expect($user->last_login_at->isToday())->toBeTrue();
});
