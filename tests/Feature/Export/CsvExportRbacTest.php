<?php

use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->analyst = User::create([
        'id' => Str::uuid(),
        'github_id' => 'analyst_id_rbac',
        'username' => 'analyst_user',
        'email' => 'analyst_rbac@insighta.local',
        'role' => 'analyst',
        'is_active' => true,
    ]);

    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'admin_id_rbac',
        'username' => 'admin_user',
        'email' => 'admin_rbac@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->analystToken = $this->analyst->createToken('access')->plainTextToken;
    $this->adminToken = $this->admin->createToken('access')->plainTextToken;
});

it('blocks analyst from accessing export (403)', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->analystToken}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $response->assertStatus(403)
             ->assertJson(['status' => 'error']);
});

it('allows admin to access export successfully', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $response->assertStatus(200);
});

it('rejects unauthorized access strictly via query params role override', function () {
    // Attempt role bypass visually passing 'role=admin' to backend expecting it evaluates wrongly
    $response = $this->withHeader('Authorization', "Bearer {$this->analystToken}")
                     ->get('/api/profiles/export?format=csv&role=admin', ['X-API-Version' => '1']);
                     
    $response->assertStatus(403);
});

it('blocks admin if they become inactive (is_active = false)', function () {
    $this->admin->update(['is_active' => false]);
    
    $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $response->assertStatus(403);
});
