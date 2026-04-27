<?php
declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\PipelineContext\FilterContext;
use Closure;

interface QueryFilterInterface
{

    public function handle(FilterContext $filterContext, Closure $next): FilterContext;

}
