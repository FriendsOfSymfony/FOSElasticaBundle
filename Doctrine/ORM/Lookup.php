<?php

namespace FOS\ElasticaBundle\Doctrine\ORM;

use Doctrine\ORM\Query;
use FOS\ElasticaBundle\Doctrine\AbstractLookup;
use FOS\ElasticaBundle\Type\TypeConfigurationInterface;

class Lookup extends AbstractLookup
{
    const ENTITY_ALIAS = 'o';

    /**
     * Returns the lookup key.
     *
     * @return string
     */
    public function getKey()
    {
        return 'orm';
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
        $hydrationMode = $configuration->isHydrate() ?
            Query::HYDRATE_OBJECT :
            Query::HYDRATE_ARRAY;

        $qb = $this->createQueryBuilder($configuration);

        $qb->andWhere($qb->expr()->in(
            sprintf('%s.%s', static::ENTITY_ALIAS, $configuration->getIdentifierProperty()),
            ':identifiers'
        ));
        $qb->setParameter('identifiers', $ids);

        return $qb->getQuery()->execute(array(), $hydrationMode);
    }

    /**
     * @param TypeConfigurationInterface $configuration
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createQueryBuilder(TypeConfigurationInterface $configuration)
    {
        $repository = $this->registry->getRepository($configuration->getModelClass());
        $method = $configuration->getRepositoryMethod();

        return $repository->{$method}(static::ENTITY_ALIAS);
    }
}
