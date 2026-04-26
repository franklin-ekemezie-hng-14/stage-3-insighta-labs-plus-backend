<?php

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::create([
        'id' => Str::uuid(),
        'github_id' => 'escalate_id',
        'username' => 'escalate_user',
        'email' => 'escalate@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    $this->profile = Profile::create([
        'name' => 'Escalation Target',
        'gender' => 'male',
        'gender_probability' => 1,
        'age' => 45,
        'age_group' => 'adult',
        'country_id' => 'NG',
        'country_name' => 'Nigeria',
        'country_probability' => 1,
    ]);

    $this->token = $this->user->createToken('access')->plainTextToken;
});

it('admin downgraded to analyst mid-session reflects updated permissions immediately', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->deleteJson("/api/profiles/{$this->profile->id}", [], ['X-API-Version' => '1'])
         ->assertSessionHasNoErrors();
         
    $this->user->update(['role' => 'analyst']);
    
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->postJson('/api/profiles', ['name' => 'Test'], ['X-API-Version' => '1'])
         ->assertStatus(403);
});

it('analyst attempting privilege escalation via payload manipulation fails', function () {
    $this->user->update(['role' => 'analyst']);
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
         ->postJson('/api/profiles', ['name' => 'Test', 'role' => 'admin'], ['X-API-Version' => '1']);
         
    $response->assertStatus(403);
});

it('inactive user reactivated mid-session regains access', function () {
    $this->user->update(['is_active' => false]);
    
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(403);
         
    $this->user->update(['is_active' => true]);
    
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(200);
});
