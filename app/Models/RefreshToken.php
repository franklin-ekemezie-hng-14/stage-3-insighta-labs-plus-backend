<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * @property User $user
 */
#[Fillable(['token'])]
class RefreshToken extends Model
{
    //

    use HasUuids;



    /*
     * -------------------------------
     * Relationships
     * -------------------------------
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /*
     * -------------------------------
     * Helpers
     * -------------------------------
     */

    public function check(string $plainToken): bool
    {
        return Hash::check($plainToken, $this->token);
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function revoke(): self
    {
        $this->setAttribute('revoked', true);
        $this->save();

        return $this;
    }


    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
