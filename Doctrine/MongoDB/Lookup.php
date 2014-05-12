<?php

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

use FOS\ElasticaBundle\Doctrine\AbstractLookup;
use FOS\ElasticaBundle\Type\TypeConfigurationInterface;

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
     * @param TypeConfigurationInterface $configuration
     * @param array $ids
     * @return array
     */
    public function lookup(TypeConfigurationInterface $configuration, array $ids)
    {
        $qb = $this->createQueryBuilder($configuration);
        $qb->hydrate($configuration->isHydrate());

        $qb->field($configuration->getIdentifierProperty())
            ->in($ids);

        return $qb->getQuery()->execute()->toArray();
    }

    /**
     * @param TypeConfigurationInterface $configuration
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    private function createQueryBuilder(TypeConfigurationInterface $configuration)
    {
        $method = $configuration->getRepositoryMethod();
        $manager = $this->registry->getManagerForClass($configuration->getModelClass());

        return $manager->{$method}($configuration->getModelClass());
    }
}
