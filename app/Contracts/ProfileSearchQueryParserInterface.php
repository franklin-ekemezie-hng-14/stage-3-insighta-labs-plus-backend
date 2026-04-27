<?php
declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\PipelineContext\ParserContext;
use Closure;

interface ProfileSearchQueryParserInterface
{

    public function handle(
        ParserContext $parserContext,
        Closure $next
    );

}
