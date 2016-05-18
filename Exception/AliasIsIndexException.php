<?php

namespace FOS\ElasticaBundle\Exception;

class AliasIsIndexException extends \Exception
{
    /**
     * @param string $indexName
     */
    public function __construct($indexName)
    {
        parent::__construct(sprintf('Expected %s to be an alias but it is an index.', $indexName));
    }
}
