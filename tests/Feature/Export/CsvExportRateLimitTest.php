<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'admin_id_rate',
        'username' => 'admin_rate',
        'email' => 'admin_rate@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    $this->token = $this->admin->createToken('access')->plainTextToken;
});

it('enforces 60 requests per minute and hits 429 after (throttle limits)', function () {
    // Clear the current system limit specifically
    RateLimiter::clear(request()->ip());
    RateLimiter::clear($this->admin->id); // Usually throttled via user ID by Sanctum

    for ($i = 0; $i < 60; $i++) {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
        $response->assertStatus(200);
    }
    
    // Request 61 should fail
    $throttleResponse = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $throttleResponse->assertStatus(429);
});
