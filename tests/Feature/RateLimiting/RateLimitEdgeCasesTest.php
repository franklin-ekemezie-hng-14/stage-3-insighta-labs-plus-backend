<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->user = User::create([
        'id' => Str::uuid(),
        'github_id' => 'edge1_uid',
        'username' => 'edge_ruser',
        'email' => 'edge@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    $this->token = $this->user->createToken('access')->plainTextToken;
    RateLimiter::clear((string) $this->user->id);
});

it('partial window exhaustion counts accurately tracking boundaries without premature resetting', function () {
    for ($i = 1; $i <= 30; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->getJson('/api/profiles', ['X-API-Version' => '1']);
    }
    
    // Force testing simulated travel halfway 
    \Carbon\Carbon::setTestNow(now()->addSeconds(30));
    
    for ($i = 1; $i <= 30; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->getJson('/api/profiles', ['X-API-Version' => '1']);
    }
    
    // Now it should hit limit if total window hasn't effectively fully reset
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(429);
         
    \Carbon\Carbon::setTestNow();
});

it('verifies explicit 429 response formatting standard (TRD equivalent stringently structured object)', function () {
    for ($i = 0; $i < 60; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->getJson('/api/profiles/search?q=test', ['X-API-Version' => '1']);
    }
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/search?q=test', ['X-API-Version' => '1']);
                     
    $response->assertStatus(429)
             ->assertJsonStructure(['status', 'message']);
});

it('identifies token refreshing maliciously or benignly DOES NOT reset rate limiting unfair advantages', function () {
    for ($i = 1; $i <= 60; $i++) {
        $this->withHeader('Authorization', "Bearer {$this->token}")
             ->getJson('/api/profiles', ['X-API-Version' => '1']);
    }
    
    // Rate limit hit
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(429);
         
    // Creates a completely brand new token tied to the identical user
    $newToken = $this->user->createToken('access_secondary')->plainTextToken;
    
    $this->withHeader('Authorization', "Bearer {$newToken}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(429); // Remains globally blocked under identity
});

it('ensures rate limiting does not regress RBAC enforcement rules logically (Regression specific)', function () {
    \Carbon\Carbon::setTestNow(now()->addMinutes(2));
    RateLimiter::clear((string) $this->user->id); // Refresh user limit organically 
    
    // Turn admin into analyst
    $this->user->update(['role' => 'analyst']);
    
    // User attempts bounded restricted operation
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->postJson('/api/profiles', [], ['X-API-Version' => '1'])
         ->assertStatus(403); // Yields correctly parsed 403 not 429
         
    \Carbon\Carbon::setTestNow();
});
