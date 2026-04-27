<?php

namespace App\Contracts;

interface PipelineContextInterface
{
    //


    /**
     * @return mixed Entity being modified as it is passed through the pipeline
     */
    public function getPassable(): mixed;

    /**
     * @return mixed The data the pipes need to operate on passed entity
     */
    public function getContext(): mixed;
}
