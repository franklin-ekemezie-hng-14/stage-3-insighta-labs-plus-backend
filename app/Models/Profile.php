<?php

namespace App\Models;

use App\DTOs\ProfileData;
use App\Enums\AgeGroup;
use App\Enums\Gender;
use App\Policies\ProfilePolicy;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



/**
 * @property string $id
 */
#[Fillable([
    'name',
    'gender', 'gender_probability',
    'age', 'age_group',
    'country_id', 'country_name', 'country_probability',
])]
#[UsePolicy(ProfilePolicy::class)]
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory, HasUuids;


    /*
     * ----------------------------------
     * Scopes
     * ----------------------------------
     */

    #[Scope]
    protected function gender(Builder $query, Gender $gender): void
    {
        $query->where('gender', $gender->value);
    }

    /*
 * ----------------------------------
 * Helpers
 * ---------------------------------
 */

    public function toProfileData(): ProfileData
    {
        return ProfileData::from($this->name)
            ->setId($this->id)
            ->setGender($this->gender)
            ->setGenderProbability($this->gender_probability)
            ->setAge($this->age)
            ->setAgeGroup($this->age_group)
            ->setCountryId($this->country_id)
            ->setCountryName($this->country_name)
            ->setCountryProbability($this->country_probability)
            ->setCreatedAt($this->created_at);
    }

    protected function casts(): array
    {
        return [
            'age_group'             => AgeGroup::class,
            'country_probability'   => 'float',
            'age'                   => 'integer',
            'gender_probability'    => 'float',
        ];
    }

}
