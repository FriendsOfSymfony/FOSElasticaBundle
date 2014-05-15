<?php

namespace FOS\ElasticaBundle\Exception;

use Exception;

class UnexpectedObjectException extends \Exception
{
    public function __construct($id)
    {
        parent::__construct(sprintf('Lookup returned an unexpected object with id %d', $id));
    }
}
