<?php

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Str;

it('deletes a profile successfully as admin', function () {
    // Support Stage 3 Auth or bypass if not implemented
    $admin = null;
    if (class_exists(User::class)) {
        try {
            $admin = User::factory()->make(['role' => 'admin']);
        } catch (\Exception $e) {
            $admin = new User(['id' => Str::uuid(), 'role' => 'admin']);
        }
    }

    $profile = Profile::create([
        'name' => 'deleteme',
        'gender' => 'male',
        'gender_probability' => 0.99,
        'age' => 25,
        'age_group' => 'adult',
        'country_id' => 'NG',
        'country_name' => 'Nigeria',
        'country_probability' => 0.85
    ]);

    $request = $this;
    if ($admin) {
        $request = $this->actingAs($admin);
    }
    
    $response = $request->deleteJson("/api/profiles/{$profile->id}", [], ['X-API-Version' => '1']);

    $response->assertStatus(204);
    $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
});

it('returns 404 when deleting a non-existent profile', function () {
    $admin = null;
    if (class_exists(User::class)) {
        try {
            $admin = User::factory()->make(['role' => 'admin']);
        } catch (\Exception $e) {
            $admin = new User(['id' => Str::uuid(), 'role' => 'admin']);
        }
    }

    $fakeId = Str::uuid()->toString();
    
    $request = $this;
    if ($admin) {
        $request = $this->actingAs($admin);
    }
    
    $response = $request->deleteJson("/api/profiles/{$fakeId}", [], ['X-API-Version' => '1']);

    $response->assertStatus(404);
});
