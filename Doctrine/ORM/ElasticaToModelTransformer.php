<?php

namespace FOQ\ElasticaBundle\Doctrine\ORM;

use FOQ\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;
use Elastica_Document;
use Doctrine\ORM\Query;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{
    /**
     * Fetch objects for theses identifier values
     *
     * @param string $class the model class
     * @param string $identifierField like 'id'
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers($class, $identifierField, array $identifierValues, $hydrate)
    {
        $hydratationMode = $hydrate ? Query::HYDRATE_OBJECT : Query::HYDRATE_ARRAY;
        $qb = $this->objectManager
            ->getRepository($class)
            ->createQueryBuilder('o');
        $qb->where($qb->expr()->in('o.'.$identifierField, $identifierValues));

        return $qb->getQuery()->setHydrationMode($hydrationMode)->execute();
    }
}
