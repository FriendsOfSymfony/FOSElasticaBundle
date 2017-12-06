<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

use Doctrine\ODM\MongoDB\Query\Builder;
use FOS\ElasticaBundle\Doctrine\SliceFetcherInterface;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;

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
     * {@inheritdoc}
     */
    public function fetch($queryBuilder, $limit, $offset, array $previousSlice, array $identifierFieldNames)
    {
        if (!$queryBuilder instanceof Builder) {
            throw new InvalidArgumentTypeException($queryBuilder, 'Doctrine\ODM\MongoDB\Query\Builder');
        }

        $lastObject = array_pop($previousSlice);

        if ($lastObject) {
            $queryBuilder
                ->field('_id')->gt($lastObject->getId())
                ->skip(0)
            ;
        } else {
            $queryBuilder->skip($offset);
        }

        return $queryBuilder
            ->limit($limit)
            ->sort(['_id' => 'asc'])
            ->getQuery()
            ->execute()
            ->toArray()
        ;
    }
}
