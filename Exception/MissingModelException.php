<?php

namespace FOS\ElasticaBundle\Exception;

use Exception;

class MissingModelException extends \Exception
{
    public function __construct($modelCount, $resultCount)
    {
        $message = sprintf('Expected to have %d models, but the lookup returned %d results', $resultCount, $modelCount);

        parent::__construct($message);
    }
}
