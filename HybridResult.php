<?php

namespace FOS\ElasticaBundle;

use Elastica\Result;

class HybridResult
{
    protected $result;
    protected $transformed;

    public function __construct(Result $result, $transformed = null)
    {
        $this->result = $result;
        $this->transformed = $transformed;
    }

    public function getTransformed()
    {
        return $this->transformed;
    }

    public function getResult()
    {
        return $this->result;
    }
}