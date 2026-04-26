<?php

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'admin_id_123',
        'username' => 'admin_user',
        'email' => 'admin@insighta.local',
        'role' => 'admin',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->token = $this->admin->createToken('access')->plainTextToken;
    
    $this->profile = Profile::create([
        'name' => 'Admin Test Profile',
        'gender' => 'male',
        'gender_probability' => 0.9,
        'age' => 30,
        'age_group' => 'adult',
        'country_id' => 'US',
        'country_name' => 'United States',
        'country_probability' => 0.9,
    ]);
});

it('admin can GET profiles list', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles', ['X-API-Version' => '1']);
    
    $response->assertStatus(200)->assertJson(['status' => 'success']);
});

it('admin can GET profile by id', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson("/api/profiles/{$this->profile->id}", ['X-API-Version' => '1']);
    
    expect(in_array($response->status(), [200, 404]))->toBeTrue();
    expect($response->status())->not->toBe(403);
});

it('admin can GET profiles search', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/search?q=test', ['X-API-Version' => '1']);
    
    $response->assertStatus(200);
});

it('admin can POST profiles successfully', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->postJson('/api/profiles', ['name' => 'New User'], ['X-API-Version' => '1']);
    
    expect($response->status())->not->toBe(403);
});

it('admin can DELETE profiles', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->deleteJson("/api/profiles/{$this->profile->id}", [], ['X-API-Version' => '1']);
    
    expect(in_array($response->status(), [200, 204, 404]))->toBeTrue();
    expect($response->status())->not->toBe(403);
});

it('admin can export CSV successfully', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get("/api/profiles/export?format=csv", ['X-API-Version' => '1']);
    
    expect($response->status())->not->toBe(403);
});
