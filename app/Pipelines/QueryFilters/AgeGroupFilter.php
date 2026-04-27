<?php
declare(strict_types=1);

namespace App\Pipelines\QueryFilters;

use App\Contracts\QueryFilterInterface;
use App\DTOs\PipelineContext\FilterContext;
use Closure;

class AgeGroupFilter implements QueryFilterInterface
{

    public function handle(FilterContext $filterContext, Closure $next): FilterContext
    {
        $query = $filterContext->getPassable();
        $filters = $filterContext->getContext();

        if (! empty($filters['age_group'])) {
            $ageGroup = strtolower($filters['age_group']);

            $query->whereRaw('LOWER(age_group) = ?', $ageGroup);
        }

        return $next($filterContext);
    }

}
