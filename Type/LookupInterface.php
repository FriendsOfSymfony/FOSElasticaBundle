<?php

namespace FOS\ElasticaBundle\Type;

/**
 * A service that provides lookup capabilities for a type.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
interface LookupInterface
{
    /**
     * Returns the lookup key.
     *
     * @return string
     */
    public function getKey();

    /**
     * Look up objects of a specific type with ids as supplied.
     *
     * @param TypeConfiguration $configuration
     * @param int[] $ids
     * @return object[]
     */
    public function lookup(TypeConfiguration $configuration, array $ids);
}
