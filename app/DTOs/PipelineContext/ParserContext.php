<?php
declare(strict_types=1);

namespace App\DTOs\PipelineContext;

use App\Support\FilterMap;

class ParserContext extends AbstractPipelineContext
{

    /** @var FilterMap $passable  */
    protected mixed $passable;

    /** @var string $context */
    protected mixed $context;

    /**
     * @inheritDoc
     */
    public function getPassable(): FilterMap
    {
        return $this->passable;
    }

    /**
     * @inheritDoc
     */
    public function getContext(): string
    {
        return $this->context;
    }
}
