<?php

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'admin_id_filter',
        'username' => 'admin_flt',
        'email' => 'admin_flt@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    
    $this->token = $this->admin->createToken('access')->plainTextToken;
    
    Profile::getQuery()->delete();
    
    Profile::create([
        'name' => 'Male Profile', 'gender' => 'male', 'gender_probability' => 1,
        'age' => 20, 'age_group' => 'adult', 'country_id' => 'US', 'country_name' => 'United States', 'country_probability' => 1,
        'created_at' => now()->subDay()
    ]);
    
    Profile::create([
        'name' => 'Female Profile NG', 'gender' => 'female', 'gender_probability' => 1,
        'age' => 30, 'age_group' => 'adult', 'country_id' => 'NG', 'country_name' => 'Nigeria', 'country_probability' => 1,
        'created_at' => now()
    ]);
});

it('filters export exactly by gender', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv&gender=male', ['X-API-Version' => '1']);
                     
    $csvOutput = $response->getContent();
    $lines = explode("\n", trim($csvOutput));
    
    // Header + 1 male profile
    expect(count($lines))->toBe(2);
    expect($lines[1])->toContain('Male Profile');
    expect($lines[1])->not->toContain('Female Profile NG');
});

it('filters export exactly by country', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv&country_id=NG', ['X-API-Version' => '1']);
                     
    $csvOutput = $response->getContent();
    expect($csvOutput)->toContain('Female Profile NG');
    expect($csvOutput)->not->toContain('Male Profile');
});

it('filters export by age bounds', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv&min_age=25', ['X-API-Version' => '1']);
                     
    $csvOutput = $response->getContent();
    expect($csvOutput)->toContain('Female Profile NG');
    expect($csvOutput)->not->toContain('Male Profile');
});

it('combines multiple filters flawlessly', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv&gender=female&country_id=NG&min_age=25', ['X-API-Version' => '1']);
                     
    $csvOutput = $response->getContent();
    $lines = explode("\n", trim($csvOutput));
    expect(count($lines))->toBe(2);
    expect($csvOutput)->toContain('Female Profile NG');
});

it('sorts export identically to dataset logic (by created_at)', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv&sort_by=created_at&order=desc', ['X-API-Version' => '1']);
                     
    $csvOutput = $response->getContent();
    $lines = explode("\n", trim($csvOutput));
    
    expect(count($lines))->toBe(3); // Wait, this includes header
    // Descending order means newest first -> Female Profile NG first
    expect($lines[1])->toContain('Female Profile NG');
    expect($lines[2])->toContain('Male Profile');
});
