<?php

namespace App\Services;

use App\DTOs\PipelineContext\ParserContext;
use App\Pipelines\NLQueryParsers\AgeGroupParser;
use App\Pipelines\NLQueryParsers\AgeParser;
use App\Pipelines\NLQueryParsers\CountryParser;
use App\Pipelines\NLQueryParsers\GenderParser;
use App\Support\FilterMap;
use Illuminate\Pipeline\Pipeline;

class NLQueryParser
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function parse(string $query): array
    {
        $filterMap = new FilterMap();

        /** @var ParserContext $parserContext */
        $parserContext = app(Pipeline::class)
            ->send(new ParserContext($filterMap, $query))
            ->through([
                AgeParser::class,
                AgeGroupParser::class,
                CountryParser::class,
                GenderParser::class,
            ])
            ->thenReturn();


        return $parserContext->getPassable()->toArray();

    }
}
