<?php
declare(strict_types=1);

namespace App\Pipelines\NLQueryParsers;

use App\Contracts\ProfileSearchQueryParserInterface;
use App\DTOs\PipelineContext\ParserContext;
use Closure;

class GenderParser implements ProfileSearchQueryParserInterface
{

    public function handle(ParserContext $parserContext, Closure $next)
    {
        $filterMap = $parserContext->getPassable();
        $query = $parserContext->getContext();

        if (preg_match('/(male|female|boy|girl)/', $query, $matches) === 1) {
            $keyword = $matches[1];

            match ($keyword) {
                'male'      => $filterMap->male(),
                'female'    => $filterMap->female(),
                'boy'       => $filterMap->male()->child(),
                'girl'      => $filterMap->female()->child(),
            };
        }

        return $next($parserContext);
    }
}
