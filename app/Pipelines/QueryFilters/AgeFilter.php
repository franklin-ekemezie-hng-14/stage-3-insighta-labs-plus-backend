<?php
declare(strict_types=1);

namespace App\Pipelines\QueryFilters;

use App\Contracts\QueryFilterInterface;
use App\DTOs\PipelineContext\FilterContext;
use Closure;

class AgeFilter implements QueryFilterInterface
{

    public function handle(FilterContext $filterContext, Closure $next): FilterContext
    {
        $query = $filterContext->getPassable();
        $filters = $filterContext->getContext();

        if (! empty($filters['min_age']) && is_numeric($filters['min_age'])) {
            $minAge = (int) $filters['min_age'];

            $query->where('age', '>=', $minAge);
        }

        if (! empty($filters['max_age']) && is_numeric($filters['max_age'])) {
            $maxAge = (int) $filters['max_age'];

            $query->where('age', '<=', $maxAge);
        }

        return $next($filterContext);
    }
}
