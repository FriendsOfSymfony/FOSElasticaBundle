<?php

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

use FOS\ElasticaBundle\Doctrine\AbstractLookup;
use FOS\ElasticaBundle\Type\TypeConfiguration;

class Lookup extends AbstractLookup
{
    /**
     * Returns the lookup key.
     *
     * @return string
     */
    public function getKey()
    {
        return 'mongodb';
    }

    /**
     * Look up objects of a specific type with ids as supplied.
     *
     * @param TypeConfiguration $configuration
     * @param array $ids
     * @return array
     */
    public function lookup(TypeConfiguration $configuration, array $ids)
    {
        $qb = $this->createQueryBuilder($configuration);
        $qb->hydrate($configuration->isHydrate());

        $qb->field($configuration->getIdentifierProperty())
            ->in($ids);

        return $qb->getQuery()->execute()->toArray();
    }

    /**
     * @param TypeConfiguration $configuration
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    private function createQueryBuilder(TypeConfiguration $configuration)
    {
        $method = $configuration->getRepositoryMethod();
        $manager = $this->registry->getManagerForClass($configuration->getModelClass());

        return $manager->{$method}($configuration->getModelClass());
    }
}
