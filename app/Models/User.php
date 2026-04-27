<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property Role $role
 * @property Carbon $last_login_at
 * @property string $id
 */
#[Fillable(['username', 'email', 'avatar_url'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasUuids, HasFactory, Notifiable, HasApiTokens;



    /*
     * -----------------------------
     * Relationships
     * ---------------------------
     */

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    /*
     * ---------------------------
     * Helpers
     * ---------------------------
     */


    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isAnalyst(): bool
    {
        return $this->role === Role::ANALYST;
    }

    public function setRole(Role $role): self
    {
        $this->setAttribute('role', $role);
        return $this;
    }

    public function isActive(): bool
    {
        return !! $this->active;
    }

    public function setGithubId(string $githubId): self
    {
        $this->setAttribute('github_id', $githubId);
        return $this;
    }

    public function updateLastLogin(): self
    {
        $this->setAttribute('last_login_at', now());
        return $this;
    }

    protected function createRefreshToken(): string
    {
        $refreshToken = Str::random(64);

        $refreshToken = RefreshToken::query()->make([
            'token' => Hash::make($refreshToken),
        ]);
        $refreshToken->setAttribute('expires_at', now()->addMinutes(3));
        $refreshToken->user()->associate($this);
        $refreshToken->save();

        return $refreshToken;
    }

    protected function createAccessToken(): string
    {
        return $this
            ->createToken('api', $this->role->abilities(), now()->addMinutes(5))
            ->plainTextToken;
    }

    public function issueTokens(): array
    {
        return [
            'access_token'  => $this->createAccessToken(),
            'refresh_token' => $this->createRefreshToken(),
        ];
    }



    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'role'          => Role::class,
            'last_login_at' => 'datetime',
        ];
    }
}
