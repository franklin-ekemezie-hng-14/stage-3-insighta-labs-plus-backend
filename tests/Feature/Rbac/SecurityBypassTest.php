<?php

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->analyst = User::create([
        'id' => Str::uuid(),
        'github_id' => 'sb_analyst',
        'username' => 'sb_analyst',
        'email' => 'sb@insighta.local',
        'role' => 'analyst',
        'is_active' => true,
    ]);
    
    $this->token = $this->analyst->createToken('access')->plainTextToken;
});

it('ensures no endpoint leakage via 404 vs 403 on delete', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->deleteJson("/api/profiles/99999999-9999-9999-9999-999999999999", [], ['X-API-Version' => '1']);
                     
    $response->assertStatus(403);
});

it('ensures no hidden access via query params', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->postJson('/api/profiles?role=admin&is_admin=true', ['name' => 'Test'], ['X-API-Version' => '1']);
                     
    $response->assertStatus(403);
});

it('ensures no role spoofing via headers or payload', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->withHeader('X-User-Role', 'admin')
                     ->withHeader('Role', 'admin')
                     ->postJson('/api/profiles', ['name' => 'Test'], ['X-API-Version' => '1']);
                     
    $response->assertStatus(403);
});

it('ensures Sanctum token cannot override role natively', function () {
    $adminToken = $this->analyst->createToken('access', ['admin'])->plainTextToken;
    
    $response = $this->withHeader('Authorization', "Bearer {$adminToken}")
                     ->postJson('/api/profiles', ['name' => 'Test'], ['X-API-Version' => '1']);
                     
    $response->assertStatus(403);
});

it('token reuse after logout fails gracefully', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->postJson('/auth/logout', [], ['X-API-Version' => '1']);
         
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles', ['X-API-Version' => '1']);
                     
    $response->assertStatus(401);
});
