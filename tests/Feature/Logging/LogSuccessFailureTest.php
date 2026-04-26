<?php

use App\Models\User;
use Illuminate\Support\Str;
use Tests\Feature\Logging\LogHelper;

beforeEach(function () {
    LogHelper::flushDatabaseLogs();
    LogHelper::interceptLogs();
    
    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'log_admin1',
        'username' => 'log_admin_user',
        'email' => 'log1@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    $this->token = $this->admin->createToken('access')->plainTextToken;
    
    $this->analyst = User::create([
        'id' => Str::uuid(),
        'github_id' => 'log_analyst1',
        'username' => 'log_analyst_user',
        'email' => 'log2@insighta.local',
        'role' => 'analyst',
        'is_active' => true,
    ]);
    $this->analystToken = $this->analyst->createToken('access')->plainTextToken;
});

it('tests successful authenticated profile fetch logs correctly', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(200);

    LogHelper::assertLogExistsForRequest('GET', '/api/profiles', 200);
});

it('tests unauthorized RBAC request (403) logs accurately securely', function () {
    $this->withHeader('Authorization', "Bearer {$this->analystToken}")
         ->postJson('/api/profiles', ['name' => 'Failed Create'], ['X-API-Version' => '1'])
         ->assertStatus(403);

    LogHelper::assertLogExistsForRequest('POST', '/api/profiles', 403);
});

it('tests missing API version header logs correctly (400) independent of business flow', function () {
    $this->withHeader('Authorization', "Bearer {$this->token}")
         ->getJson('/api/profiles/search?q=test')
         ->assertStatus(400);

    LogHelper::assertLogExistsForRequest('GET', '/api/profiles/search', 400);
});

it('tests unauthenticated request correctly logs 401 status', function () {
    $this->getJson('/api/profiles/export?format=csv', ['X-API-Version' => '1'])
         ->assertStatus(401);

    LogHelper::assertLogExistsForRequest('GET', '/api/profiles/export', 401);
});

it('tests missing or invalid auth token request still logs securely', function () {
    $this->withHeader('Authorization', 'Bearer faulty_token_123')
         ->getJson('/api/profiles', ['X-API-Version' => '1'])
         ->assertStatus(401);
         
    LogHelper::assertLogExistsForRequest('GET', '/api/profiles', 401);
});
