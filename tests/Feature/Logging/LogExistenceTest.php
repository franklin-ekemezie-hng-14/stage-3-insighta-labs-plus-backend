<?php

use App\Models\User;
use Illuminate\Support\Str;
use Tests\Feature\Logging\LogHelper;

beforeEach(function () {
    LogHelper::flushDatabaseLogs();
    LogHelper::interceptLogs();
});

it('every request creates a log entry (no request is missed)', function () {
    $initialLogsCount = count(LogHelper::getAllLogs());

    for ($i = 0; $i < 5; $i++) {
        $this->getJson('/api/profiles/search?q=miss', ['X-API-Version' => '1']);
    }

    $finalLogsCount = count(LogHelper::getAllLogs());
    expect($finalLogsCount - $initialLogsCount)->toBe(5);
});

it('log entry exists securely in implementation layer for unauthorized accesses', function () {
    $response = $this->getJson('/api/profiles', ['X-API-Version' => '1']);
    $response->assertStatus(401);

    LogHelper::assertLogExistsForRequest('GET', '/api/profiles', 401);
});

it('logs extremely fast requests efficiently without bypassing tracker', function () {
    // Basic root or extremely fast API check that just fails 404
    $this->getJson('/api/non-existent-fast-endpoint', ['X-API-Version' => '1'])->assertStatus(404);
    
    LogHelper::assertLogExistsForRequest('GET', '/api/non-existent-fast-endpoint', 404);
});
