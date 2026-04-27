<?php
declare(strict_types=1);

namespace App\Pipelines\QueryFilters;

use App\Contracts\QueryFilterInterface;
use App\DTOs\PipelineContext\FilterContext;
use Closure;

class CountryIdFilter implements QueryFilterInterface
{

    public function handle(FilterContext $filterContext, Closure $next): FilterContext
    {
        $query = $filterContext->getPassable();
        $filters = $filterContext->getContext();

        if (! empty($filters['country_id'])) {
            $countryId = strtolower($filters['country_id']);

            $query->whereRaw('LOWER(country_id) = ?', $countryId);
        }

        return $next($filterContext);
    }
}
