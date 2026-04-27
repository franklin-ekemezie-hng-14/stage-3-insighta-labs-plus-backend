<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {

    Sanctum::actingAs(
        User::factory()->create(['role' => Role::ADMIN, 'is_active' => false]),
        Role::ADMIN->abilities()
    );
});

it('inactive user cannot access ANY endpoint', function () {
    $endpoints = [
        ['GET', '/api/profiles'],
        ['POST', '/api/profiles', ['name' => 'Test']],
        ['GET', '/api/profiles/export?format=csv']
    ];

    foreach ($endpoints as $e) {
        $method = $e[0];
        $endpoint = $e[1];
        $payload = $e[2] ?? [];

        $response = $this->json($method, $endpoint, $payload, ['X-API-Version' => '1']);

        $response->assertStatus(403)
                 ->assertJson(['status' => 'error']);
    }
});

it('always returns 403 even with valid token', function () {
    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);

    $response->assertStatus(403);
});
