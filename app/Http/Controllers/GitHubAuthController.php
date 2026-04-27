<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Exceptions\HTTPException;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;

class GitHubAuthController extends Controller
{
    //

    public function redirect()
    {

        /** @var GithubProvider $gitHubProvider */
        $gitHubProvider = Socialite::driver('github');

        return $gitHubProvider->stateless()->enablePKCE()->redirect();
    }

    /**
     * @throws HTTPException
     */
    public function callback()
    {

        /** @var GithubProvider $githubProvider */
        $githubProvider = Socialite::driver('github');
        $githubUser = $githubProvider->stateless()->enablePKCE()->user();

        $user = User::query()->where('github_id', $githubUser->getId())->first();
        if (! $user) {

            $user = User::query()->make([
                'username'      => $githubUser->getNickname(),
                'email'         => $githubUser->getEmail(),
                'avatar_url'    => $githubUser->getAvatar(),
            ])
                ->setGithubId($githubUser->getId())
                ->setRole(Role::ANALYST);
        } else {

            $user->update([
                'username'      => $githubUser->getNickname(),
                'email'         => $githubUser->getEmail(),
                'avatar_url'    => $githubUser->getAvatar(),
            ]);
        }

        $user->updateLastLogin();
        $user->save();

        [
            'refresh_token' => $refreshToken,
            'access_token'  => $accessToken,
        ] = $user->issueTokens();

        return response()->json([
            'status'        => 'success',
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
        ]);
    }

}
