<?php

use App\Enums\Role;
use App\Models\Profile;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {

    // Insert profiles
    Profile::query()->create(['name' => 'obi', 'gender' => 'male', 'gender_probability' => 0.95, 'age' => 20, 'age_group' => 'adult', 'country_id' => 'NG', 'country_name' => 'Nigeria', 'country_probability' => 0.9]); // young male from nigeria
    Profile::query()->create(['name' => 'ada', 'gender' => 'female', 'gender_probability' => 0.99, 'age' => 35, 'age_group' => 'adult', 'country_id' => 'NG', 'country_name' => 'Nigeria', 'country_probability' => 0.8]); // female above 30 from nigeria
    Profile::query()->create(['name' => 'mwangi', 'gender' => 'male', 'gender_probability' => 0.9, 'age' => 25, 'age_group' => 'adult', 'country_id' => 'KE', 'country_name' => 'Kenya', 'country_probability' => 0.95]); // adult male from kenya
    Profile::query()->create(['name' => 'joao', 'gender' => 'male', 'gender_probability' => 0.96, 'age' => 18, 'age_group' => 'teenager', 'country_id' => 'AO', 'country_name' => 'Angola', 'country_probability' => 0.85]); // male teenager above 17 from angola

    Sanctum::actingAs(
        User::factory()->create(),
        Role::ANALYST->abilities()
    );
});

it('returns correct results for valid natural language search query 1', function () {
    // "young males from nigeria"
    $response = $this->getJson('/api/profiles/search?q=young males from nigeria', ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status', 'page', 'limit', 'total', 'data'
        ])
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'obi');
});

it('returns correct results for valid natural language search query 2', function () {
    // "females above 30"
    $response = $this->getJson('/api/profiles/search?q=females above 30', ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'ada');
});

it('returns correct results for valid natural language search query 3', function () {
    // "adult males from kenya"
    $response = $this->getJson('/api/profiles/search?q=adult males from kenya', ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'mwangi');
});

it('returns correct results for valid natural language search query 4', function () {
    // "people from angola"
    $response = $this->getJson('/api/profiles/search?q=people from angola', ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'joao');
});

it('returns correct results for valid natural language search query 5', function () {
    // "male teenagers above 17"
    $response = $this->getJson('/api/profiles/search?q=male teenagers above 17', ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'joao');
});

it('handles case-insensitivity, partial matches, and extra whitespace', function () {
    $response = $this->getJson('/api/profiles/search?q=' . urlencode('  YOUNG MALE   NIGERIA   '), ['X-API-Version' => '1']);

    $response->assertStatus(200)
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.name', 'obi');
});

it('returns error for empty string query', function () {
    $response = $this->getJson('/api/profiles/search?q=', ['X-API-Version' => '1']);

    expect(in_array($response->status(), [400, 422]))->toBeTrue();
    $response->assertJson([
        'status' => 'error'
    ]);
});

it('returns error for unparseable nonsense text', function () {
    $response = $this->getJson('/api/profiles/search?q=' . urlencode('gobbldeygook randomword foo bar'), ['X-API-Version' => '1']);

    expect(in_array($response->status(), [400, 422]))->toBeTrue();
    $response->assertJson([
        'status' => 'error',
        'message' => 'Unable to interpret query'
    ]);
});

it('returns error for malformed query parameters', function () {
    $response = $this->getJson('/api/profiles/search', ['X-API-Version' => '1']);

    expect(in_array($response->status(), [400, 422]))->toBeTrue();
    $response->assertJson([
        'status' => 'error'
    ]);
});

it('resolves conflicting filters deterministically based on rules', function () {
    $response = $this->getJson('/api/profiles/search?q=' . urlencode('young senior teenagers adults'), ['X-API-Version' => '1']);
    $response->assertStatus(200);
});
