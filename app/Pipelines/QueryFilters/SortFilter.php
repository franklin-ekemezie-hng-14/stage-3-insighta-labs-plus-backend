<?php
declare(strict_types=1);

namespace App\Pipelines\QueryFilters;

use App\Contracts\QueryFilterInterface;
use App\DTOs\PipelineContext\FilterContext;
use Closure;

class SortFilter implements QueryFilterInterface
{

    public function handle(FilterContext $filterContext, Closure $next): FilterContext
    {

        $query = $filterContext->getPassable();
        $filters = $filterContext->getContext();

        if (! empty($filters['sort_by']) && ! empty($filters['order'])) {
            $validSortByFields = ['age', 'created_at', 'gender_probability'];
            $validOrder = ['asc', 'desc'];

            $sortByField = (string) $filters['sort_by'];
            $order = (string) $filters['order'];

            if (
                in_array($sortByField, $validSortByFields) &&
                in_array($order, $validOrder)
            ) {

                $query->orderBy($sortByField, $order);
            }

        }

        return $next($filterContext);

    }

}
