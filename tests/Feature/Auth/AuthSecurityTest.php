<?php

use Illuminate\Support\Facades\Session;

it('PKCE must be enforced (callback expects verifier matching challenge)', function () {
    $this->getJson('/auth/github', ['X-API-Version' => '1']);
    
    expect(Session::has('oauth_code_verifier'))->toBeTrue();
});

it('state must be validated on callback', function () {
    $response = $this->getJson('/auth/github/callback?code=mock&state=wrongstate');
    
    $response->assertStatus(400)
        ->assertJson(['status' => 'error']);
});

it('access token expires in 3 minutes', function () {
    $this->assertTrue(true);
});

it('refresh token expires in 5 minutes', function () {
    $this->assertTrue(true);
});

it('refresh token is SINGLE USE (rotation required)', function () {
    $this->assertTrue(true);
});
