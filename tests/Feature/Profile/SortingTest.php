<?php

use App\Models\Profile;

beforeEach(function () {
    Profile::create(['name' => 'a', 'gender' => 'male', 'gender_probability' => 0.8, 'age' => 45, 'age_group' => 'adult', 'country_id' => 'US', 'country_name' => 'US', 'country_probability' => 0.9, 'created_at' => now()->subDays(3)]);
    Profile::create(['name' => 'b', 'gender' => 'female', 'gender_probability' => 0.99, 'age' => 15, 'age_group' => 'teenager', 'country_id' => 'CA', 'country_name' => 'CA', 'country_probability' => 0.8, 'created_at' => now()->subDays(1)]);
    Profile::create(['name' => 'c', 'gender' => 'male', 'gender_probability' => 0.6, 'age' => 30, 'age_group' => 'adult', 'country_id' => 'UK', 'country_name' => 'UK', 'country_probability' => 0.85, 'created_at' => now()->subDays(2)]);
});

it('sorts by age asc', function () {
    $response = $this->getJson('/api/profiles?sort_by=age&order=asc', ['X-API-Version' => '1']);
    
    $response->assertStatus(200);
    expect($response->json('data.0.name'))->toBe('b');
    expect($response->json('data.1.name'))->toBe('c');
    expect($response->json('data.2.name'))->toBe('a');
});

it('sorts by age desc', function () {
    $response = $this->getJson('/api/profiles?sort_by=age&order=desc', ['X-API-Version' => '1']);
    
    $response->assertStatus(200);
    expect($response->json('data.0.name'))->toBe('a');
    expect($response->json('data.1.name'))->toBe('c');
    expect($response->json('data.2.name'))->toBe('b');
});

it('sorts by created_at', function () {
    $response = $this->getJson('/api/profiles?sort_by=created_at&order=desc', ['X-API-Version' => '1']);
    
    $response->assertStatus(200);
    expect($response->json('data.0.name'))->toBe('b');
    expect($response->json('data.1.name'))->toBe('c');
    expect($response->json('data.2.name'))->toBe('a');
});

it('sorts by gender_probability', function () {
    $response = $this->getJson('/api/profiles?sort_by=gender_probability&order=desc', ['X-API-Version' => '1']);
    
    $response->assertStatus(200);
    expect($response->json('data.0.name'))->toBe('b'); // 0.99
    expect($response->json('data.1.name'))->toBe('a'); // 0.8
    expect($response->json('data.2.name'))->toBe('c'); // 0.6
});

it('does not crash on invalid sort field', function () {
    $response = $this->getJson('/api/profiles?sort_by=invalid_field&order=asc', ['X-API-Version' => '1']);
    
    // Either gracefully ignores (200) or throws validation error (422) depending on implementation
    expect(in_array($response->status(), [200, 422]))->toBeTrue();
});
