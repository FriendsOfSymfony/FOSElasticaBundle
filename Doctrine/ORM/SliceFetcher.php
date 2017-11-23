<?php

namespace FOS\ElasticaBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;
use FOS\ElasticaBundle\Doctrine\SliceFetcherInterface;

@trigger_error(sprintf('The %s class is deprecated since version 4.1 and will be removed in 5.0.', SliceFetcher::class), E_USER_DEPRECATED);

/**
 * @deprecated since 4.1 will be removed in 5.x.
 * 
 * Fetches a slice of objects.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
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
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
        }

        /*
         * An orderBy DQL  part is required to avoid feching the same row twice.
         * @see http://stackoverflow.com/questions/6314879/does-limit-offset-length-require-order-by-for-pagination
         * @see http://www.postgresql.org/docs/current/static/queries-limit.html
         * @see http://www.sqlite.org/lang_select.html#orderby
         */
        $orderBy = $queryBuilder->getDQLPart('orderBy');
        if (empty($orderBy)) {
            $rootAliases = $queryBuilder->getRootAliases();

            foreach ($identifierFieldNames as $fieldName) {
                $queryBuilder->addOrderBy($rootAliases[0].'.'.$fieldName);
            }
        }

        return $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
