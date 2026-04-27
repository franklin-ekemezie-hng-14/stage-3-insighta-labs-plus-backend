<?php
declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\DTOs\PipelineContext\FilterContext;
use App\DTOs\ProfileData;
use App\Models\Profile;
use App\Pipelines\QueryFilters\AgeFilter;
use App\Pipelines\QueryFilters\AgeGroupFilter;
use App\Pipelines\QueryFilters\CountryIdFilter;
use App\Pipelines\QueryFilters\CountryProbabilityFilter;
use App\Pipelines\QueryFilters\GenderFilter;
use App\Pipelines\QueryFilters\GenderProbabilityFilter;
use App\Pipelines\QueryFilters\SortFilter;
use App\Support\PaginatedCollection;
use Illuminate\Pipeline\Pipeline;

class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    public function findByName(string $name): ?ProfileData
    {
        return Profile::query()
            ->where('name', $name)
            ->first()
            ?->toProfileData();
    }

    public function findById(string $id): ?ProfileData
    {
        return Profile::query()
            ->where('uuid', $id)
            ->first()
            ?->toProfileData();
    }

    public function getAll(int $limit, int $page=1, array $filters=[]): PaginatedCollection
    {

        $query = Profile::query();

        if ($filters) {

            $filterContext = new FilterContext($query, $filters);

            /** @var FilterContext $filterContext */
            $filterContext = app(Pipeline::class)
                ->send($filterContext)
                ->through([
                    GenderFilter::class,
                    AgeGroupFilter::class,
                    CountryIdFilter::class,
                    AgeFilter::class,
                    GenderProbabilityFilter::class,
                    CountryProbabilityFilter::class,
                    SortFilter::class,
                ])
                ->thenReturn();

            $query = $filterContext->getPassable();
        }

        $transform = fn (Profile $profile) => $profile->toProfileData();

        $result = $query->paginate($limit, page: $page);

        return PaginatedCollection::fromPaginator($result, $transform);

    }

    public function count(): int
    {
        return Profile::query()->count();
    }

    public function create(array $data): ProfileData
    {
        /** @var Profile $profile */
        $profile = Profile::query()->create($data);

        return $profile->toProfileData();
    }

    public function delete(string $id): bool
    {
        return !! (Profile::query()
            ->where('id', $id)
            ->delete());
    }
}
