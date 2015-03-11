<?php

namespace FOS\ElasticaBundle\Doctrine\PHPCR;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Elastica\Result;
use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;
use Doctrine\ORM\Query;

use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ElasticaToDocumentTransformer extends AbstractElasticaToModelTransformer
{
    /**
     * Fetch objects for theses identifier values
     *
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, $hydrate)
    {
        return iterator_to_array($this->registry
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass)
            ->findMany($identifierValues));
    }
}
