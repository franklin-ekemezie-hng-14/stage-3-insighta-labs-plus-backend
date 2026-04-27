<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {

    Sanctum::actingAs(
        User::factory()->create(['role' => Role::ADMIN]),
        Role::ADMIN->abilities()
    );

});
it('creates a new profile successfully with mocked external APIs', function () {
    Http::fake([
        'https://api.genderize.io*' => Http::response(['name' => 'ella', 'gender' => 'female', 'probability' => 0.99, 'count' => 1234]),
        'https://api.agify.io*' => Http::response(['name' => 'ella', 'age' => 46, 'count' => 1234]),
        'https://api.nationalize.io*' => Http::response(['name' => 'ella', 'country' => [['country_id' => 'GH', 'probability' => 0.85]]])
    ]);

    $response = $this->postJson('/api/profiles', ['name' => 'ella'], ['X-API-Version' => '1']);

    $response->assertStatus(201)
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
                'name' => 'ella',
                'gender' => 'female',
                'age' => 46,
                'age_group' => 'adult',
                'country_id' => 'GH',
                'country_name' => 'Ghana',
            ]
        ]);

    $data = $response->json('data');
    expect(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $data['id']))->toBe(1)
        ->and(preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?Z$/', $data['created_at']))->toBe(1);

    $this->assertDatabaseHas('profiles', ['name' => 'ella', 'age' => 46]);
});

it('ensures idempotency by returning existing profile and not duplicating', function () {
    Http::fake([
        'https://api.genderize.io*' => Http::response(['name' => 'ella', 'gender' => 'female', 'probability' => 0.99, 'count' => 1234]),
        'https://api.agify.io*' => Http::response(['name' => 'ella', 'age' => 46, 'count' => 1234]),
        'https://api.nationalize.io*' => Http::response(['name' => 'ella', 'country' => [['country_id' => 'GH', 'probability' => 0.85]]])
    ]);

    // First request
    $response1 = $this->postJson('/api/profiles', ['name' => 'ella'], ['X-API-Version' => '1']);
    $response1->assertStatus(201);

    // Second request with same name
    $response2 = $this->postJson('/api/profiles', ['name' => 'ella'], ['X-API-Version' => '1']);
    $response2->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Profile already exists'
        ]);

    expect($response1->json('data.id'))->toBe($response2->json('data.id'));
    $this->assertDatabaseCount('profiles', 1);
});
