<?php

namespace App\Http\Controllers;

use App\Actions\GetProfilesAction;
use App\Models\Profile;
use App\Services\NLQueryParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ProfileSearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //

//        $request->user()->can('view-any', Profile::class);

        $validator = Validator::make($request->all(), [
            'q'     => ['required', 'string', 'max:255'],
            'page'  => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Invalid query parameters',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $limit = $request->integer('limit', 10);
        $page = $request->integer('page', 1);

        $searchQuery = (string) $request->string('q')->lower();
        $nlQueryParser = new NLQueryParser();
        $filters = $nlQueryParser->parse($searchQuery);

        if (empty($filters)) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Unable to interpret query'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = app(GetProfilesAction::class)
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
}
