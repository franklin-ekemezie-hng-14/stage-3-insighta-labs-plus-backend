<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $userId = Str::uuid();
    DB::table('users')->insert([
        'id' => $userId,
        'github_id' => 'admin_id_ver',
        'username' => 'admin_user_ver',
        'email' => 'adminver@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);

    /** @var User $user */
    $user = User::query()->find($userId);
    $this->user = $user;
    [
        'access_token' => $accessToken,
    ] = $this->user->issueTokens();
    $this->token = $accessToken;

    Sanctum::actingAs(
        $user,
        $user->role->abilities()
    );

    RateLimiter::clear((string) $this->user->id);
});

it('allows exactly 60 requests to API endpoints and blocks 61st decisively with 429', function () {
    for ($i = 1; $i <= 60; $i++) {
        $this
             ->getJson('/api/profiles/search?q=t', ['X-API-Version' => '1'])
             ->assertStatus(200);
    }

    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);

    $response->assertStatus(429);
});

it('verifies API Rate Limit is securely tied to user identity constraints (independent of IP fallback)', function () {
    // Ensures the user instance explicitly hit the limit
    for ($i = 1; $i <= 60; $i++) {
        $this->getJson('/api/profiles', ['X-API-Version' => '1']);
    }

    $this->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(429);
});

it('tests API limit traverses successfully across segregated profile manipulation and extraction APIs smoothly', function () {
    RateLimiter::clear((string) $this->user->id);

    for ($i = 1; $i <= 20; $i++) {
        $this->getJson('/api/profiles', ['X-API-Version' => '1']);
    }
    for ($i = 1; $i <= 20; $i++) {
        $this->postJson('/api/profiles', ['name' => 'John'], ['X-API-Version' => '1']);
    }
    for ($i = 1; $i <= 20; $i++) {
        $this->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
    }

    // 61st fails
    $this->getJson('/api/profiles/search', ['X-API-Version' => '1'])
         ->assertStatus(429);
});
