<?php

namespace App\Http\Controllers;

use App\Actions\CreateProfileAction;
use App\Actions\GetProfilesAction;
use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\Enums\AgeGroup;
use App\Enums\Gender;
use App\Exceptions\ExternalApiException;
use App\Http\Requests\ListProfileRequest;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\CreateProfileResource;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListProfileRequest $request)
    {
        //

        // $request->user()->can('view-any', Profile::class);


        $data       = $request->validated();
        $limit      = $request->integer('limit', 10);
        $page       = $request->integer('page', 1);
        $filters    = $request->only(array_keys($data));

        $data       = app(GetProfilesAction::class)
            ->execute($limit, $page, $filters);

        return response()->json([
            'status'        => 'success',
            'page'          => $data['page'],
            'limit'         => $data['limit'],
            'total'         => $data['total'],
            'total_pages'   => $data['total_pages'],
            'links'         => $data['links'],
            'data'          => $data['data'],
        ]);

    }

    /**
     * Store a newly created resource in storage.
     * @throws ExternalApiException Exception handled at `\bootstrap\app.php`
     */
    public function store(StoreProfileRequest $request)
    {
        //

        $request->user()->can('create', Profile::class);

        $name = (string) $request->input('name');

        $profile = app(CreateProfileAction::class)->execute($name);

        if ($profile->isRetrieved()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Profile already exists',
                'data' => CreateProfileResource::make($profile),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'status' => 'success',
            'data' => CreateProfileResource::make($profile),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Profile $profile)
    {
        //

        $request->user()->can('view', $profile);

        return response()->json([
            'status' => 'success',
            'data' => $profile->toResource(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request, Profile $profile)
    {
        //

        $request->user()->can('update', $profile);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Profile $profile, ProfileRepositoryInterface $profiles)
    {
        //

        $request->user()->can('delete', $profile);

        if (! $profiles->delete($profile->id)) {

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete profile',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile deleted',
        ], Response::HTTP_NO_CONTENT);
    }
}
