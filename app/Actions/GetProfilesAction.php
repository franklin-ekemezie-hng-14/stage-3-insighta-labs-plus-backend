<?php
declare(strict_types=1);

namespace App\Actions;

use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\DTOs\ProfileData;

readonly class GetProfilesAction
{

    public function __construct(
        private ProfileRepositoryInterface $profiles
    )
    {
    }


    public function execute(int $limit, int $page=1, array $filters=[]): array
    {
        $keys = [
            'id',           'name',
            'gender',       'gender_probability',
            'age',          'age_group',
            'country_id',   'country_name',         'country_probability',
            'created_at'
        ];

        $transformCallback = function (ProfileData $profile) use ($keys) {
            return collect($profile->toArray())->only($keys)->toArray();
        };

        $data = $this->profiles->getAll($limit, $page, $filters);

        return [
            'page'          => $data->getPage(),
            'limit'         => $data->getLimit(),
            'total'         => $data->getTotal(),
            'total_pages'   => $data->getTotalPages(),
            'links'         => $data->getLinks(),
            'data'      => $data->getData($transformCallback)->all(),
        ];

    }
}
