<?php

namespace FOQ\ElasticaBundle;

use Elastica_Result;

class HybridResult
{
    protected $result;
    protected $transformed;

    public function __construct(Elastica_Result $result, $transformed = null)
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