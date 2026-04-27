<?php
declare(strict_types=1);

namespace App\Pipelines\NLQueryParsers;

use App\Contracts\ProfileSearchQueryParserInterface;
use App\DTOs\PipelineContext\ParserContext;
use Closure;

class AgeParser implements ProfileSearchQueryParserInterface
{

    public function handle(ParserContext $parserContext, Closure $next)
    {
        $filterMap = $parserContext->getPassable();
        $query = $parserContext->getContext();

        if (preg_match('/(above|below|under) (\d+)/', $query, $matches) === 1) {
            $qualifier = $matches[1];
            $age = (int) $matches[2];

            match ($qualifier) {
                'above' => $filterMap->minAge($age + 1),
                'below',
                'under' => $filterMap->maxAge($age - 1),
            };
        }

        if (preg_match('/(young|youth|old|older)/', $query, $matches) === 1) {
            $keyword = $matches[1];

            match ($keyword) {
                'young',
                'youth'     => $filterMap->young(),
                'old',
                'elder'     => $filterMap->old(),
            };
        }

        return $next($parserContext);
    }
}
