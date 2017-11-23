<?php

namespace FOS\ElasticaBundle\Doctrine\PHPCR;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;
use FOS\ElasticaBundle\Doctrine\SliceFetcherInterface;

@trigger_error(sprintf('The %s class is deprecated since version 4.1 and will be removed in 5.0.', SliceFetcher::class), E_USER_DEPRECATED);

/**
 * @deprecated since 4.1 will be removed in 5.x.
 * 
 * Fetches a slice of objects.
 */
class SliceFetcher implements SliceFetcherInterface
{
    /**
     * This method should remain in sync with Provider::fetchSlice until that method is deprecated and
     * removed.
     *
     * {@inheritdoc}
     */
    public function fetch($queryBuilder, $limit, $offset, array $previousSlice, array $identifierFieldNames)
    {
        if (!$queryBuilder instanceof QueryBuilder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder');
        }

        return $queryBuilder
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getResult()
            ->toArray();
    }
}
