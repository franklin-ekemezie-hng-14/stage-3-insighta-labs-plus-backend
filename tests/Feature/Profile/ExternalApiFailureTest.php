<?php

use Illuminate\Support\Facades\Http;

it('returns 502 when Genderize returns null gender', function () {
    Http::fake([
        'api.genderize.io/*' => Http::response(['name' => 'unknown', 'gender' => null, 'probability' => 0, 'count' => 0]),
        'api.agify.io/*' => Http::response(['name' => 'unknown', 'age' => 25, 'count' => 100]),
        'api.nationalize.io/*' => Http::response(['name' => 'unknown', 'country' => [['country_id' => 'NG', 'probability' => 1]]])
    ]);

    $response = $this->postJson('/api/profiles', ['name' => 'unknown'], ['X-API-Version' => '1']);

    $response->assertStatus(502)
        ->assertJson([
            'status' => 'error',
            'message' => 'Genderize returned an invalid response'
        ]);
        
    $this->assertDatabaseCount('profiles', 0);
});

it('returns 502 when Agify returns null age', function () {
    Http::fake([
        'api.genderize.io/*' => Http::response(['name' => 'ageless', 'gender' => 'male', 'probability' => 1, 'count' => 100]),
        'api.agify.io/*' => Http::response(['name' => 'ageless', 'age' => null, 'count' => 0]),
        'api.nationalize.io/*' => Http::response(['name' => 'ageless', 'country' => [['country_id' => 'NG', 'probability' => 1]]])
    ]);

    $response = $this->postJson('/api/profiles', ['name' => 'ageless'], ['X-API-Version' => '1']);

    $response->assertStatus(502)
        ->assertJson([
            'status' => 'error',
            'message' => 'Agify returned an invalid response'
        ]);
        
    $this->assertDatabaseCount('profiles', 0);
});

it('returns 502 when Nationalize returns empty country list', function () {
    Http::fake([
        'api.genderize.io/*' => Http::response(['name' => 'nowhere', 'gender' => 'male', 'probability' => 1, 'count' => 100]),
        'api.agify.io/*' => Http::response(['name' => 'nowhere', 'age' => 30, 'count' => 100]),
        'api.nationalize.io/*' => Http::response(['name' => 'nowhere', 'country' => []])
    ]);

    $response = $this->postJson('/api/profiles', ['name' => 'nowhere'], ['X-API-Version' => '1']);

    $response->assertStatus(502)
        ->assertJson([
            'status' => 'error',
            'message' => 'Nationalize returned an invalid response'
        ]);
        
    $this->assertDatabaseCount('profiles', 0);
});
