<?php

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->admin = User::query()->create([
        'id' => Str::uuid(),
        'github_id' => 'admin_id_edg',
        'username' => 'admin_edg',
        'email' => 'admin_edg@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->token = $this->admin->createToken('access')->plainTextToken;
    Profile::query()->getQuery()->delete();
});

it('handles empty dataset gracefully (header only exported)', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);

    $response->assertStatus(200);
    $csvOutput = $response->getContent();
    $lines = explode("\n", trim($csvOutput));

    expect(count($lines))->toBe(1); // Only header should exist
});

it('securely handles special characters (commas, quotes) escaping', function () {
    Profile::create([
        'name' => 'Jane "Doe", Queen',
        'gender' => 'female',
        'gender_probability' => 0.9,
        'age' => 40,
        'age_group' => 'adult',
        'country_id' => 'CA',
        'country_name' => 'Canada, EH',
        'country_probability' => 0.9,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);

    $csvOutput = $response->getContent();

    $lines = explode("\n", trim($csvOutput));
    $parsedLine = str_getcsv($lines[1]);

    expect($parsedLine[1])->toBe('Jane "Doe", Queen');
    expect($parsedLine[7])->toBe('Canada, EH');
});

it('handles large dataset efficiently in batch context (simulated chunk size performance safety)', function () {
    for ($i = 0; $i < 50; $i++) {
        Profile::query()->create([
            'name' => "Batch User {$i}", 'gender' => 'male', 'gender_probability' => 1,
            'age' => 20, 'age_group' => 'adult', 'country_id' => 'US', 'country_name' => 'USA', 'country_probability' => 1,
        ]);
    }

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);

    $response->assertStatus(200);
    $lines = explode("\n", trim($response->getContent()));
    expect(count($lines))->toBe(51); // 50 items + 1 header
});

it('allows null values for optional probability columns logically handling them without error', function () {
    Profile::query()->create([
        'name' => 'No Probable',
        'gender' => 'male',
        'gender_probability' => null, // Assuming optional internally or manually seeded
        'age' => 50,
        'age_group' => 'adult',
        'country_id' => 'US',
        'country_name' => 'USA',
        'country_probability' => null,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);

    $csvOutput = $response->getContent();
    $lines = explode("\n", trim($csvOutput));
    $parsed = str_getcsv($lines[1]);

    expect($parsed[3])->toBe(''); // Empty equivalent of null in CSV
});
