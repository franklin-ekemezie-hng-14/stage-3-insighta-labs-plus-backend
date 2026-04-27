<?php

namespace App\Http\Controllers;

use App\Actions\GetProfilesAction;
use App\Http\Requests\ListProfileRequest;
use Illuminate\Http\Request;

class ProfileExportController extends Controller
{
    //

    public function __invoke(ListProfileRequest $request)
    {

        $data       = $request->validated();
        $limit      = $request->integer('limit', 10);
        $page       = $request->integer('page', 1);
        $filters    = $request->only(array_keys($data));

        $data       = app(GetProfilesAction::class)
            ->execute($limit, $page, $filters);
        $profiles   = $data['data'];

        $filename = 'profiles_' . now()->timestamp . '.csv';

        return response()->streamDownload(function () use ($profiles, $filename) {

            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'id', 'name',
                'gender', 'gender_probability',
                'age', 'age_group',
                'country_name', 'country_probability',
                'created_at',
            ]);

            foreach ($profiles as $profile) {
                fputcsv($handle, [
                    $profile['id'],
                    $profile['name'],
                    $profile['gender'],
                    $profile['gender_probability'],
                    $profile['age'],
                    $profile['age_group'],
                    $profile['country_name'],
                    $profile['country_probability'],
                    $profile['created_at'],
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);

    }
}
