<?php
declare(strict_types=1);

namespace App\DTOs\PipelineContext;

use Illuminate\Database\Eloquent\Builder;

class FilterContext extends AbstractPipelineContext
{

    /** @var Builder $passable  */
    protected mixed $passable;

    /** @var array $context */
    protected mixed $context = [];

    public function getPassable(): Builder
    {
        return $this->passable;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
