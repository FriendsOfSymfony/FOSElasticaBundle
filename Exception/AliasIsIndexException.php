<?php

namespace FOS\ElasticaBundle\Exception;

class AliasIsIndexException extends \Exception
{
    public function __construct($indexName)
    {
        parent::__construct(sprintf('Expected alias %s instead of index', $indexName));
    }
}

