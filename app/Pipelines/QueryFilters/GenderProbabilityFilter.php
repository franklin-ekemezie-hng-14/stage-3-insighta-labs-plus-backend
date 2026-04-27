<?php
declare(strict_types=1);

namespace App\Pipelines\QueryFilters;

use App\Contracts\QueryFilterInterface;
use App\DTOs\PipelineContext\FilterContext;
use Closure;

class GenderProbabilityFilter implements QueryFilterInterface
{

    public function handle(FilterContext $filterContext, Closure $next): FilterContext
    {

        $query = $filterContext->getPassable();
        $filters = $filterContext->getContext();

        if (! empty($filters['min_gender_probability']) && is_numeric($filters['min_gender_probability'])) {
            $minGenderProbability = (float) $filters['min_gender_probability'];

            $query->where('gender_probability', '>=', $minGenderProbability);
        }

        if (! empty($filters['max_gender_probability']) && is_numeric($filters['max_gender_probability'])) {
            $maxGenderProbability = (float) $filters['max_gender_probability'];

            $query->where('gender_probability', '<=', $maxGenderProbability);
        }

        return $next($filterContext);
    }
}
