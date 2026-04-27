<?php

namespace App\Http\Resources;

use App\DTOs\CreateProfileResult;

class CreateProfileResource
{
    /**
     * Create a new class instance.
     */
    public static function make(CreateProfileResult $createProfileResult): array
    {
        //

        $profile = $createProfileResult->getProfile();

        return [
            'id'                    => $profile->getId(),
            'name'                  => $profile->getName(),
            'gender'                => $profile->getGender(),
            'gender_probability'    => $profile->getGenderProbability(),
            'age'                   => $profile->getAge(),
            'age_group'             => $profile->getAgeGroup()->value,
            'country_id'            => $profile->getCountryId(),
            'country_name'          => $profile->getCountryName(),
            'country_probability'   => $profile->getCountryProbability(),
            'created_at'            => $profile->getCreatedAt()->toISOString(),
        ];
    }
}
