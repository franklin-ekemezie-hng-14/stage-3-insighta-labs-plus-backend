<?php
declare(strict_types=1);

namespace App\Pipelines\NLQueryParsers;

use App\Contracts\ProfileSearchQueryParserInterface;
use App\DTOs\PipelineContext\ParserContext;
use Closure;

class AgeGroupParser implements ProfileSearchQueryParserInterface
{

    public function handle(ParserContext $parserContext, Closure $next)
    {

        $filterMap = $parserContext->getPassable();
        $query = $parserContext->getContext();

        if (preg_match('/(child|teenager|teen|adult|senior)/', $query, $matches) === 1) {
            $keyword = $matches[1];

            match ($keyword) {
                'child'     => $filterMap->child(),
                'teenager',
                'teen'      => $filterMap->teenager(),
                'adult'     => $filterMap->adult(),
                'senior'    => $filterMap->senior(),
            };
        }

        return $next($parserContext);
    }
}
