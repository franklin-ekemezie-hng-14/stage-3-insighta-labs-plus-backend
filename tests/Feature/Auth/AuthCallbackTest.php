<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;

beforeEach(function () {
    Session::put('state', 'valid_state_123');
    Session::put('code_verifier', 'valid_verifier');
});

it('successful OAuth login creates new user', function () {

    Socialite::fake('github', (new User())->map([
        'id' => 12345,
        'nickname' => 'test_user',
        'email' => 'test@example.com',
        'avatar_url' => 'https://example.com/avatar.jpg',
    ]));

    $response = $this->getJson('/auth/github/callback');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'access_token',
            'refresh_token'
        ])
        ->assertJsonFragment(['status' => 'success']);

    $this->assertDatabaseHas('users', [
        'github_id' => '12345',
        'username' => 'test_user',
        'role' => 'analyst'
    ]);
});

it('existing user is updated (not duplicated)', function () {
    DB::table('users')->insert([
        'id' => Str::uuid(),
        'github_id' => '12345',
        'username' => 'old_name',
        'email' => 'old@example.com',
        'role' => 'admin',
        'is_active' => true,
        'last_login_at' => now()->subDay(),
        'created_at' => now(),
        'updated_at' => now()
    ]);


    Socialite::fake('github', (new User())->map([
        'id' => 12345,
        'nickname' => 'new_name',
        'email' => 'new@example.com',
        'avatar_url' => 'https://example.com/avatar.jpg'
    ])->setRefreshToken('gh_fake_token'));

    $response = $this->getJson('/auth/github/callback?code=fake_code&state=valid_state_123');

    $response->assertStatus(200);

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('users', [
        'github_id' => '12345',
        'username' => 'new_name',
        'role' => 'admin'
    ]);
});

it('invalid state is rejected', function () {
    $response = $this->getJson('/auth/github/callback?code=fake_code&state=invalid_state');

    $response->assertStatus(400)
        ->assertJson([
            'status' => 'error',
            'message' => 'Invalid state'
        ]);
});

// Can't test invalid code since Socialite uses Guzzle internally, and we can't
// fake the Guzzle API

//it('invalid code is rejected', function () {
//
//
//    Socialite::fake('github', (new User())->map([]));
//
//    $response = $this->getJson('/auth/github/callback?state=valid_state_123');
//
//    $response->assertStatus(400)
//        ->assertJson(['status' => 'error']);
//});

it('user is assigned default role = analyst', function () {

    Socialite::fake('github', (new User())->map([
        'id' => 999,
        'nickname' => 'analyst_user',
        'email' => 'user@example.com'
    ]));


    $this->getJson('/auth/github/callback?code=fake_code&state=valid_state_123');

    $this->assertDatabaseHas('users', [
        'github_id' => '999',
        'role' => 'analyst',
    ]);
});

it('access_token and refresh_token are returned', function () {

    Socialite::fake('github', (new User())->map([
        'id' => 111,
        'nickname' => 'user111',
        'email' => 'user111@example.com'
    ]));

    $response = $this->getJson('/auth/github/callback?code=fake_code&state=valid_state_123');

    $response->assertJsonStructure(['access_token', 'refresh_token']);
});

it('last_login_at is updated', function () {

    $userId = Str::uuid();
    $githubId = '777';
    $now = now();

    DB::table('users')->insert([
        'id' => $userId,
        'github_id' => $githubId,
        'username' => 'last_login',
        'email' => 'last@example.com',
        'role' => 'analyst',
        'is_active' => true,
        'last_login_at' => $now->subDays(5),
        'created_at' => $now,
        'updated_at' => $now
    ]);

    Socialite::fake('github', (new User())->map([
        'id' => $githubId,
        'nickname' => 'last_login',
        'email' => 'last_login@example.com',
    ]));

    $this->getJson('/auth/github/callback?code=fake_code&state=valid_state_123');

    $this->assertDatabaseHas('users', [
        'github_id' => $githubId
    ]);

    $user = \App\Models\User::query()->find($userId);
    expect($user->last_login_at->isToday())->toBeTrue();


});
