<?php
declare(strict_types=1);

namespace App\Pipelines\NLQueryParsers;

use App\Contracts\ProfileSearchQueryParserInterface;
use App\DTOs\PipelineContext\ParserContext;
use App\Enums\Country;
use Closure;

class CountryParser implements ProfileSearchQueryParserInterface
{

    public function handle(ParserContext $parserContext, Closure $next)
    {
        $filterMap = $parserContext->getPassable();
        $query = $parserContext->getContext();

        foreach (Country::cases() as $country) {
            $countryName = $country->name();

            if (str_contains($query, strtolower($countryName))) {
                $filterMap->country($country);
            }

        }

        return $next($parserContext);
    }
}
