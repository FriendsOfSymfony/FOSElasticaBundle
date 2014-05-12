<?php

namespace FOS\ElasticaBundle\Propel;

use Doctrine\Common\Util\Inflector;
use FOS\ElasticaBundle\Type\LookupInterface;
use FOS\ElasticaBundle\Type\TypeConfigurationInterface;

class Lookup implements LookupInterface
{
    /**
     * Returns the lookup key.
     *
     * @return string
     */
    public function getKey()
    {
        return 'propel';
    }

    /**
     * Look up objects of a specific type with ids as supplied.
     *
     * @param TypeConfigurationInterface $configuration
     * @param int[] $ids
     * @return object[]
     */
    public function lookup(TypeConfigurationInterface $configuration, array $ids)
    {
        $query = $this->createQuery($configuration, $ids);

        if (!$configuration->isHydrate()) {
            return $query->toArray();
        }

        return $query->find();
    }

    /**
     * Create a query to use in the findByIdentifiers() method.
     *
     * @param TypeConfigurationInterface $configuration
     * @param array $ids
     * @return \ModelCriteria
     */
    protected function createQuery(TypeConfigurationInterface $configuration, array $ids)
    {
        $queryClass = $configuration->getModelClass() . 'Query';
        $query = $queryClass::create();
        $filterMethod = 'filterBy' . Inflector::camelize($configuration->getIdentifierProperty());

        return $query->$filterMethod($ids);
    }
}
