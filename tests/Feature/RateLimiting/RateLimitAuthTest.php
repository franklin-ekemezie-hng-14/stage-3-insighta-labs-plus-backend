<?php

use Illuminate\Support\Facades\RateLimiter;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    RateLimiter::clear(request()->ip());
});

it('allows exactly 10 requests to auth endpoints and blocks the 11th with 429', function () {
    for ($i = 1; $i <= 10; $i++) {
        // Hitting auth endpoint without parameters may yield 400 or redirect 302, 
        // regardless it consumes rate limit normally
        $res = getJson('/auth/github', ['X-API-Version' => '1']);
        expect(in_array($res->status(), [302, 400, 200]))->toBeTrue();
    }
    
    getJson('/auth/github', ['X-API-Version' => '1'])->assertStatus(429);
});

it('resets auth rate limit strictly after 1 minute window expiration', function () {
    for ($i = 1; $i <= 10; $i++) {
        getJson('/auth/github', ['X-API-Version' => '1']);
    }
    
    getJson('/auth/github', ['X-API-Version' => '1'])->assertStatus(429);
    
    // Simulate time passing natively
    \Carbon\Carbon::setTestNow(now()->addMinutes(1)->addSeconds(1));
    
    getJson('/auth/github', ['X-API-Version' => '1'])->assertStatus(302); // 302 assumes Github oauth redirect
    
    // Reset test time
    \Carbon\Carbon::setTestNow();
});

it('applies limit uniformly traversing various auth routes (/auth/*) preventing burst traffic abuse', function () {
    RateLimiter::clear(request()->ip());
    
    for ($i = 1; $i <= 5; $i++) {
        getJson('/auth/github', ['X-API-Version' => '1']); // 5 hits
    }
    
    for ($i = 1; $i <= 5; $i++) {
        postJson('/auth/refresh', ['refresh_token' => 'invalid'], ['X-API-Version' => '1']); // +5 hits
    }
    
    // 11th request to any auth route fails
    getJson('/auth/github/callback', ['X-API-Version' => '1'])->assertStatus(429);
});
