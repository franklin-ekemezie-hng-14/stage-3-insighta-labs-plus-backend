<?php

use App\Models\Profile;

beforeEach(function () {
    for ($i = 1; $i <= 15; $i++) {
        Profile::create([
            'name' => "person{$i}",
            'gender' => 'male',
            'gender_probability' => 0.9,
            'age' => 20,
            'age_group' => 'adult',
            'country_id' => 'NG',
            'country_name' => 'Nigeria',
            'country_probability' => 0.9
        ]);
    }
});

it('returns correct pagination structure with default page and limit', function () {
    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'page',
            'limit',
            'total',
            'data'
        ])
        ->assertJson([
            'status' => 'success',
            'page' => 1,
            'limit' => 10,
            'total' => 15
        ]);
        
    expect(count($response->json('data')))->toBe(10);
});

it('respects page and limit parameters', function () {
    $response = $this->getJson('/api/profiles?page=2&limit=5', ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'page' => 2,
            'limit' => 5,
            'total' => 15
        ]);

    expect(count($response->json('data')))->toBe(5);
});

it('respects max limit of 50', function () {
    // Generate up to 55 to exceed the limit
    for ($i = 16; $i <= 55; $i++) {
        Profile::create([
            'name' => "person{$i}",
            'gender' => 'male',
            'gender_probability' => 0.9,
            'age' => 20,
            'age_group' => 'adult',
            'country_id' => 'NG',
            'country_name' => 'Nigeria',
            'country_probability' => 0.9
        ]);
    }

    $response = $this->getJson('/api/profiles?limit=100', ['X-API-Version' => '1']);

    $response->assertStatus(200);
    expect(count($response->json('data')))->toBeLessThanOrEqual(50);
});

it('returns empty array when page is out of bounds safely', function () {
    $response = $this->getJson('/api/profiles?page=100', ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'page' => 100,
            'data' => []
        ]);
});
