<?php

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->analyst = User::create([
        'id' => Str::uuid(),
        'github_id' => 'analyst_id_123',
        'username' => 'analyst_user',
        'email' => 'analyst@insighta.local',
        'role' => 'analyst',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->token = $this->analyst->createToken('access')->plainTextToken;
    
    $this->profile = Profile::create([
        'name' => 'Analyst Test Profile',
        'gender' => 'female',
        'gender_probability' => 0.8,
        'age' => 25,
        'age_group' => 'adult',
        'country_id' => 'UK',
        'country_name' => 'United Kingdom',
        'country_probability' => 0.85,
    ]);
});

it('analyst can GET profiles list', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles', ['X-API-Version' => '1']);
    
    $response->assertStatus(200);
});

it('analyst can GET profile by id', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson("/api/profiles/{$this->profile->id}", ['X-API-Version' => '1']);
    
    expect(in_array($response->status(), [200, 404]))->toBeTrue();
    expect($response->status())->not->toBe(403);
});

it('analyst can GET profiles search', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/search?q=test', ['X-API-Version' => '1']);
    
    $response->assertStatus(200);
});

it('analyst CANNOT POST profiles', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->postJson('/api/profiles', ['name' => 'New User'], ['X-API-Version' => '1']);
    
    $response->assertStatus(403)->assertJson(['status' => 'error']);
});

it('analyst CANNOT DELETE profiles', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->deleteJson("/api/profiles/{$this->profile->id}", [], ['X-API-Version' => '1']);
    
    $response->assertStatus(403)->assertJson(['status' => 'error']);
});

it('analyst CANNOT export CSV', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson("/api/profiles/export?format=csv", ['X-API-Version' => '1']);
    
    $response->assertStatus(403)->assertJson(['status' => 'error']);
});
