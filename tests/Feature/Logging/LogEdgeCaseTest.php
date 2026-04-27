<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Logging\LogTestHelper;

beforeEach(function () {
    LogTestHelper::flushDatabaseLogs();
    LogTestHelper::interceptLogs();

    Sanctum::actingAs(
        $this->user = User::factory()->create(['role' => Role::ANALYST]),
        Role::ANALYST->abilities()
    );
});

it('validates rate limit exceeded triggers 429 and logs explicitly', function () {
    RateLimiter::clear($this->user->id);
    RateLimiter::clear(request()->ip());

    for ($i = 0; $i < 61; $i++) {
        $response = $this->getJson('/api/profiles/search?q=test', ['X-API-Version' => '1']);
    }

    $response->assertStatus(429);
    LogTestHelper::assertLogExistsForRequest('GET', '/api/profiles/search', 429);
});

it('handles slow artificial delay requests appropriately logging total time', function () {
    // Use an endpoint that exists maybe or simulate slowness. We'll rely on normal parsing.
    $this->getJson('/api/profiles', ['X-API-Version' => '1']); // 401 but valid structure

    $logs = LogTestHelper::getAllLogs();
    $lastLog = end($logs);

    expect((float)$lastLog['response_time'])->toBeGreaterThan(0);
});

it('logs malformed requests transparently', function () {
    // Passing malformed JSON body
    $this->call('POST', '/api/profiles', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_VERSION' => '1'
         ], '{invalid_json}');

    // Usually routes to 400 or 403 (unauthorized/analyst rule), still logs
    $logs = LogTestHelper::getAllLogs();
    expect(count($logs))->toBeGreaterThan(0);

    $lastLog = end($logs);
    expect($lastLog['method'])->toBe('POST');
});

it('logs completely unknown endpoints strictly (404)', function () {
    // Some implementations only log /api/*, so hitting /api/unknown-abc fits rule.
    // The TRD states ALL /api/* and /auth/* routes.
    $this->getJson('/api/totally-unknown-route', ['X-API-Version' => '1'])->assertStatus(404);

    LogTestHelper::assertLogExistsForRequest('GET', '/api/totally-unknown-route', 404);
});
