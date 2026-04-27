<?php
declare(strict_types=1);

namespace App\DTOs\PipelineContext;

use App\Contracts\PipelineContextInterface;

abstract class AbstractPipelineContext implements PipelineContextInterface
{

    protected mixed $passable;
    protected mixed $context;

    public function __construct(
        mixed $passable,
        mixed $context
    )
    {
        $this->passable = $passable;
        $this->context = $context;
    }
}
