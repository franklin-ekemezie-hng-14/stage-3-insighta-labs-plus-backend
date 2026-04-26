<?php

use App\Models\Profile;

beforeEach(function () {
    Profile::create(['name' => 'john', 'gender' => 'male', 'gender_probability' => 0.95, 'age' => 45, 'age_group' => 'adult', 'country_id' => 'US', 'country_name' => 'United States', 'country_probability' => 0.9]);
    Profile::create(['name' => 'jane', 'gender' => 'female', 'gender_probability' => 0.99, 'age' => 30, 'age_group' => 'adult', 'country_id' => 'CA', 'country_name' => 'Canada', 'country_probability' => 0.8]);
    Profile::create(['name' => 'kofi', 'gender' => 'male', 'gender_probability' => 0.80, 'age' => 14, 'age_group' => 'teenager', 'country_id' => 'GH', 'country_name' => 'Ghana', 'country_probability' => 0.95]);
    Profile::create(['name' => 'mary', 'gender' => 'female', 'gender_probability' => 0.96, 'age' => 65, 'age_group' => 'senior', 'country_id' => 'UK', 'country_name' => 'United Kingdom', 'country_probability' => 0.85]);
});

it('filters by gender case insensitive', function () {
    $response = $this->getJson('/api/profiles?gender=Male', ['X-API-Version' => '1']);
    
    $response->assertStatus(200);
    
    $names = collect($response->json('data'))->pluck('name');
    expect($names)->toContain('john', 'kofi');
    expect($names)->not->toContain('jane', 'mary');
});

it('filters by country_id', function () {
    $response = $this->getJson('/api/profiles?country_id=CA', ['X-API-Version' => '1']);
    
    $response->assertStatus(200)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'jane');
});

it('filters by age_group', function () {
    $response = $this->getJson('/api/profiles?age_group=senior', ['X-API-Version' => '1']);
    
    $response->assertStatus(200)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'mary');
});

it('filters by age range', function () {
    $response = $this->getJson('/api/profiles?min_age=20&max_age=50', ['X-API-Version' => '1']);
    
    $response->assertStatus(200)
        ->assertJsonPath('total', 2);
        
    $names = collect($response->json('data'))->pluck('name');
    expect($names)->toContain('john', 'jane');
});

it('filters by minimum probabilities', function () {
    $response = $this->getJson('/api/profiles?min_gender_probability=0.96', ['X-API-Version' => '1']);
    
    $response->assertStatus(200)
        ->assertJsonPath('total', 2); // jane and mary
});

it('combines multiple filters using AND logic and accurate subset', function () {
    $response = $this->getJson('/api/profiles?gender=female&min_age=60', ['X-API-Version' => '1']);
    
    $response->assertStatus(200)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'mary');
});

it('returns empty result when filter combination does not match', function () {
    $response = $this->getJson('/api/profiles?gender=male&age_group=senior', ['X-API-Version' => '1']);
    
    $response->assertStatus(200)
        ->assertJsonPath('total', 0)
        ->assertJsonPath('data', []);
});
