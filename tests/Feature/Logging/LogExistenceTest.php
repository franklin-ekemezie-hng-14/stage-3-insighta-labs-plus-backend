<?php

use App\Models\User;
use Illuminate\Support\Str;
use Tests\Feature\Logging\LogTestHelper;

beforeEach(function () {
    LogTestHelper::flushDatabaseLogs();
    LogTestHelper::interceptLogs();
});

it('every request creates a log entry (no request is missed)', function () {
    $initialLogsCount = count(LogTestHelper::getAllLogs());

    for ($i = 0; $i < 5; $i++) {
        $this->getJson('/api/profiles/search?q=miss', ['X-API-Version' => '1']);
    }

    $finalLogsCount = count(LogTestHelper::getAllLogs());
    expect($finalLogsCount - $initialLogsCount)->toBe(5);
});

it('log entry exists securely in implementation layer for unauthorized accesses', function () {
    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);
    $response->assertStatus(401);

    LogTestHelper::assertLogExistsForRequest('GET', '/api/profiles', 401);
});

it('logs extremely fast requests efficiently without bypassing tracker', function () {
    // Basic root or extremely fast API check that just fails 404
    $this->getJson('/api/non-existent-fast-endpoint', ['X-API-Version' => '1'])->assertStatus(404);

    LogTestHelper::assertLogExistsForRequest('GET', '/api/non-existent-fast-endpoint', 404);
});
