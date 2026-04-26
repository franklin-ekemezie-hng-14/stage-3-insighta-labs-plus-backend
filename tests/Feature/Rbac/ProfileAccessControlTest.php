<?php

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->analyst = User::create([
        'id' => Str::uuid(),
        'github_id' => 'pa_analyst',
        'username' => 'pa_analyst',
        'email' => 'paa@insighta.local',
        'role' => 'analyst',
        'is_active' => true,
    ]);
    
    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'pa_admin',
        'username' => 'pa_admin',
        'email' => 'pad@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->analystToken = $this->analyst->createToken('access')->plainTextToken;
    $this->adminToken = $this->admin->createToken('access')->plainTextToken;
    
    $this->profile = Profile::create([
        'name' => 'PAC Target',
        'gender' => 'male',
        'gender_probability' => 1,
        'age' => 45,
        'age_group' => 'adult',
        'country_id' => 'US',
        'country_name' => 'United States',
        'country_probability' => 1,
    ]);
});

it('ensures RBAC is not bypassable via direct route calls', function () {
    $this->withHeader('Authorization', "Bearer {$this->analystToken}")
         ->deleteJson("/api/profiles/{$this->profile->id}", [], ['X-API-Version' => '1'])
         ->assertStatus(403);
});

it('verifies consistent enforcement across profiles create', function () {
    $this->withHeader('Authorization', "Bearer {$this->analystToken}")
         ->postJson('/api/profiles', ['name' => 'Test'], ['X-API-Version' => '1'])
         ->assertStatus(403);
         
    $resp = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
         ->postJson('/api/profiles', ['name' => 'Test'], ['X-API-Version' => '1']);
    expect($resp->status())->not->toBe(403);
});
