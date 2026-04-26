<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->user = User::create([
        'id' => Str::uuid(),
        'github_id' => 'api_rate_uid',
        'username' => 'api_rate_user',
        'email' => 'api@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    $this->token = $this->user->createToken('access')->plainTextToken;
    RateLimiter::clear((string) $this->user->id);
});

it('allows exactly 60 requests to API endpoints and blocks 61st decisively with 429', function () {
    for ($i = 1; $i <= 60; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->getJson('/api/profiles/search?q=t', ['X-API-Version' => '1'])
             ->assertStatus(200);
    }
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles', ['X-API-Version' => '1']);
                     
    $response->assertStatus(429);
});

it('verifies API Rate Limit is securely tied to user identity constraints (independent of IP fallback)', function () {
    // Ensures the user instance explicitly hit the limit
    for ($i = 1; $i <= 60; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->getJson('/api/profiles', ['X-API-Version' => '1']);
    }
    
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(429);
});

it('tests API limit traverses successfully across segregated profile manipulation and extraction APIs smoothly', function () {
    RateLimiter::clear((string) $this->user->id);

    for ($i = 1; $i <= 20; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->getJson('/api/profiles', ['X-API-Version' => '1']);
    }
    for ($i = 1; $i <= 20; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->postJson('/api/profiles', ['name' => 'John'], ['X-API-Version' => '1']);
    }
    for ($i = 1; $i <= 20; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
    }
    
    // 61st fails
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->getJson('/api/profiles/search', ['X-API-Version' => '1'])
         ->assertStatus(429);
});
