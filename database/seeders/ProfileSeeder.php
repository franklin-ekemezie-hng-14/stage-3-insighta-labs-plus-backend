<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //


        $seedDataFilePath = database_path('seeds/seed_profiles.json');
        $seedData = json_decode(file_get_contents($seedDataFilePath), true);
        $profiles = $seedData['profiles'];

        foreach ($profiles as $profile) {

            $data = [
                'name'                  => $profile['name'],

                'gender'                => $profile['gender'],
                'gender_probability'    => $profile['gender_probability'],

                'age'                   => $profile['age'],
                'age_group'             => $profile['age_group'],

                'country_id'            => $profile['country_id'],
                'country_name'          => $profile['country_name'],
                'country_probability'   => $profile['country_probability'],
            ];

            Profile::query()->updateOrCreate($data, $data);

        }
    }
}
