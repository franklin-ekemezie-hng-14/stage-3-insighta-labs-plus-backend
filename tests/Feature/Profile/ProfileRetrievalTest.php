<?php

use App\Models\Profile;
use Illuminate\Support\Str;

it('retrieves a single profile successfully', function () {
    $profile = Profile::create([
        'name' => 'emmanuel',
        'gender' => 'male',
        'gender_probability' => 0.99,
        'age' => 25,
        'age_group' => 'adult',
        'country_id' => 'NG',
        'country_name' => 'Nigeria',
        'country_probability' => 0.85
    ]);

    $response = $this->getJson("/api/profiles/{$profile->id}", ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'id', 'name', 'gender', 'gender_probability', 'age', 'age_group',
                'country_id', 'country_name', 'country_probability', 'created_at'
            ]
        ])
        ->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $profile->id,
                'name' => 'emmanuel',
                'gender' => 'male'
            ]
        ]);
});

it('returns 404 when retrieving a non-existent profile', function () {
    $fakeId = Str::uuid()->toString();
    $response = $this->getJson("/api/profiles/{$fakeId}", ['X-API-Version' => '1']);

    $response->assertStatus(404)
        ->assertJson([
            'status' => 'error',
            'message' => 'Profile not found'
        ]);
});
