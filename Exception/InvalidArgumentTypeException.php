<?php

namespace FOS\ElasticaBundle\Exception;

class InvalidArgumentTypeException extends \InvalidArgumentException
{
    /**
     * @param string $expectedType
     */
    public function __construct($value, $expectedType)
    {
        parent::__construct(sprintf('Expected argument of type "%s", "%s" given', $expectedType, is_object($value) ? get_class($value) : gettype($value)));
    }
}
