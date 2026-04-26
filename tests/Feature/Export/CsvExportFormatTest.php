<?php

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->admin = User::create([
        'id' => Str::uuid(),
        'github_id' => 'admin_id_fmt',
        'username' => 'admin_fmt',
        'email' => 'admin_fmt@insighta.local',
        'role' => 'admin',
        'is_active' => true,
    ]);
    $this->token = $this->admin->createToken('access')->plainTextToken;
    
    Profile::getQuery()->delete();

    $this->profile = Profile::create([
        'name' => 'Format Test',
        'gender' => 'female',
        'gender_probability' => 0.85,
        'age' => 28,
        'age_group' => 'adult',
        'country_id' => 'NG',
        'country_name' => 'Nigeria',
        'country_probability' => 0.9,
    ]);
});

it('returns correct headers and valid CSV content_type', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $response->assertStatus(200);
    expect($response->headers->get('Content-Type'))->toContain('text/csv');
    // TRD requires exact attachment formatting profiles_<timestamp>.csv
    expect($response->headers->get('Content-Disposition'))->toMatch('/attachment; filename="profiles_\d+\.csv"/');
});

it('preserves strict column order and data matches database perfectly', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->get('/api/profiles/export?format=csv', ['X-API-Version' => '1']);
                     
    $csvOutput = $response->getContent();
    $lines = explode("\n", trim($csvOutput));
    
    // Header check
    $expectedHeader = "id,name,gender,gender_probability,age,age_group,country_id,country_name,country_probability,created_at";
    expect(str_replace(' ', '', strtolower(trim($lines[0]))))->toBe(str_replace(' ', '', $expectedHeader));
    
    // Data check
    $dataLine = str_getcsv($lines[1]);
    expect($dataLine[0])->toBe((string) $this->profile->id);
    expect($dataLine[1])->toBe($this->profile->name);
    expect($dataLine[2])->toBe($this->profile->gender);
    expect((float)$dataLine[3])->toBe((float)$this->profile->gender_probability);
    expect((int)$dataLine[4])->toBe((int)$this->profile->age);
    expect($dataLine[5])->toBe($this->profile->age_group);
    expect($dataLine[6])->toBe($this->profile->country_id);
    expect($dataLine[7])->toBe($this->profile->country_name);
    expect((float)$dataLine[8])->toBe((float)$this->profile->country_probability);
    // 9th index is created_at
});

it('rejects completely missing format parameter', function () {
     $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/export', ['X-API-Version' => '1']);
                     
     // Missing format query param
     expect(in_array($response->status(), [400, 422]))->toBeTrue(); 
});

it('rejects invalid format parameter (e.g. pdf)', function () {
     $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                     ->getJson('/api/profiles/export?format=pdf', ['X-API-Version' => '1']);
                     
     expect(in_array($response->status(), [400, 422]))->toBeTrue();
});
