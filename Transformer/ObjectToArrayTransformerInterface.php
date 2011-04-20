<?php

namespace FOQ\ElasticaBundle\Transformer;

interface ObjectToArrayTransformerInterface
{
    /**
     * Transforms an object into an array having the required keys
     *
     * @param object $object the object to convert
     * @param array $requiredKeys the keys we want to have in the returned array
     * @return array
     **/
    public function transform($object, array $requiredKeys);
}
