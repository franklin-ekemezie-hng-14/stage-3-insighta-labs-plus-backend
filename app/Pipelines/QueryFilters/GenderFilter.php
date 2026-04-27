<?php
declare(strict_types=1);

namespace App\Pipelines\QueryFilters;

use App\Contracts\QueryFilterInterface;
use App\DTOs\PipelineContext\FilterContext;
use Closure;

class GenderFilter implements QueryFilterInterface
{

    public function handle(FilterContext $filterContext, Closure $next): FilterContext
    {

        $query = $filterContext->getPassable();
        $filters = $filterContext->getContext();

        if (! empty($filters['gender'])) {
            $gender = strtolower($filters['gender']);

            $query->whereRaw('LOWER(gender) = ?', $gender);
        }

        return $next($filterContext);
    }
}
