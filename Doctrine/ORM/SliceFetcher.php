<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Doctrine\SliceFetcherInterface;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;

/**
 * Fetches a slice of objects.
 *
 * @author Thomas Prelot <tprelot@gmail.com>
 */
class SliceFetcher implements SliceFetcherInterface
{
    /** @var int $lastId */
    private $lastId = 0;

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

        $rootAlias = $queryBuilder->getRootAliases()[0];

        /*
         * An orderBy DQL  part is required to avoid feching the same row twice.
         * @see http://stackoverflow.com/questions/6314879/does-limit-offset-length-require-order-by-for-pagination
         * @see http://www.postgresql.org/docs/current/static/queries-limit.html
         * @see http://www.sqlite.org/lang_select.html#orderby
         */
        $orderBy = $queryBuilder->getDQLPart('orderBy');
        if (empty($orderBy)) {
            foreach ($identifierFieldNames as $fieldName) {
                $queryBuilder->addOrderBy("$rootAlias.$fieldName");
            }
        }

        $results = $queryBuilder
            ->where("$rootAlias.id > $this->lastId")
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        $this->lastId = (end($results))->getId();
        reset($results);
        return $results;
    }
}
