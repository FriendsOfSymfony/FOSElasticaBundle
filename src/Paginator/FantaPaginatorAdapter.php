<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Paginator;

use Pagerfanta\Adapter\AdapterInterface;

class FantaPaginatorAdapter implements AdapterInterface
{
    private $adapter;

    public function __construct(PaginatorAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        return $this->adapter->getTotalHits();
    }

    /**
     * Returns Aggregations.
     *
     * @return mixed
     *
     * @api
     */
    public function getAggregations()
    {
        return $this->adapter->getAggregations();
    }

    /**
     * Returns Suggestions.
     *
     * @return mixed
     *
     * @api
     */
    public function getSuggests()
    {
        return $this->adapter->getSuggests();
    }

    /**
     * Returns a slice of the results.
     *
     * @param int $offset The offset
     * @param int $length The length
     */
    public function getSlice($offset, $length): iterable
    {
        return $this->adapter->getResults($offset, $length)->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxScore()
    {
        return $this->adapter->getMaxScore();
    }
}
