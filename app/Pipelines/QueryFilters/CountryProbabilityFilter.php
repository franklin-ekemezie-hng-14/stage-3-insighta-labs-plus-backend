<?php
declare(strict_types=1);

namespace App\Pipelines\QueryFilters;

use App\Contracts\QueryFilterInterface;
use App\DTOs\PipelineContext\FilterContext;
use Closure;

class CountryProbabilityFilter implements QueryFilterInterface
{

    public function handle(FilterContext $filterContext, Closure $next): FilterContext
    {
        $query = $filterContext->getPassable();
        $filters = $filterContext->getContext();

        if (! empty($filters['min_country_probability']) && is_numeric($filters['min_country_probability'])) {
            $minCountryProbability = (float) $filters['min_country_probability'];

            $query->where('country_probability', '>=', $minCountryProbability);
        }

        if (! empty($filters['max_country_probability']) && is_numeric($filters['max_country_probability'])) {
            $maxCountryProbability = (float) $filters['max_country_probability'];

            $query->where('country_probability', '<=', $maxCountryProbability);
        }

        return $next($filterContext);
    }
}
