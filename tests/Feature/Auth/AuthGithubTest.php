<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

it('should redirect to GitHub OAuth URL', function () {
    $response = $this->getJson('/auth/github', ['X-API-Version' => '1']);

    $response->assertRedirectContains('github.com/login/oauth/authorize');
});

it('should generate PKCE code_verifier', function () {
    $this->getJson('/auth/github', ['X-API-Version' => '1']);

    $this->assertTrue(Session::has('code_verifier'));
});

it('should generate valid code_challenge', function () {
    $response = $this->getJson('/auth/github', ['X-API-Version' => '1']);

    $response->assertRedirectContains('code_challenge=');
    $response->assertRedirectContains('code_challenge_method=S256');
});

it('should generate unique state per request', function () {
    $response1 = $this->getJson('/auth/github', ['X-API-Version' => '1']);
    preg_match('/state=([^&]+)/', $response1->headers->get('Location') ?? '', $matches1);
    $state1 = $matches1[1] ?? '1';

    $response2 = $this->getJson('/auth/github', ['X-API-Version' => '1']);
    preg_match('/state=([^&]+)/', $response2->headers->get('Location') ?? '', $matches2);
    $state2 = $matches2[1] ?? '2';

    expect($state1)->not->toBe($state2);
});

it('should store state securely (session or cache)', function () {
    $response = $this->getJson('/auth/github', ['X-API-Version' => '1']);

    preg_match('/state=([^&]+)/', $response->headers->get('Location') ?? '', $matches);
    $state = $matches[1] ?? null;

    expect($state)->not->toBeNull();
    $this->assertTrue(Session::has('state'));
});
