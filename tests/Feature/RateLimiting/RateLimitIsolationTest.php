<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->userA = User::create([
        'id' => Str::uuid(),
        'github_id' => 'iso1_uid',
        'username' => 'user_a',
        'email' => 'usera@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    $this->tokenA = $this->userA->createToken('access')->plainTextToken;
    
    $this->userB = User::create([
        'id' => Str::uuid(),
        'github_id' => 'iso2_uid',
        'username' => 'user_b',
        'email' => 'userb@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    $this->tokenB = $this->userB->createToken('access')->plainTextToken;
    
    RateLimiter::clear((string) $this->userA->id);
    RateLimiter::clear((string) $this->userB->id);
    RateLimiter::clear(request()->ip());
});

it('multi-user isolation ensures User A hitting limit strictly DOES NOT affect User B', function () {
    // User A hits limit
    for ($i = 1; $i <= 60; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->tokenA}")
             ->getJson('/api/profiles', ['X-API-Version' => '1']);
    }
    
    // User A is blocked securely
    $this->withHeader('Authorization', "Bearer {$this->tokenA}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(429);
         
    // User B runs unhindered showing total token isolation bucket scaling
    $this->withHeader('Authorization', "Bearer {$this->tokenB}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(200);
});

it('independent rate limit buckets persist even under assumed same networking configurations', function () {
    // Both users operate concurrently under same HTTP testing engine
    
    for ($i = 1; $i <= 30; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->tokenA}")
             ->getJson('/api/profiles', ['X-API-Version' => '1']);
             
        $this->withHeader('Authorization', "Bearer {$this->tokenB}")
             ->getJson('/api/profiles', ['X-API-Version' => '1']);
    }
    
    // No one hits the combined 60 threshold individually
    $this->withHeader('Authorization', "Bearer {$this->tokenA}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(200);
         
    $this->withHeader('Authorization', "Bearer {$this->tokenB}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(200);
});
