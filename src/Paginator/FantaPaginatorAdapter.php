<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Paginator;

use Pagerfanta\Adapter\AdapterInterface;

class FantaPaginatorAdapter implements AdapterInterface
{
    private $adapter;

    /**
     * @param \FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface $adapter
     */
    public function __construct(PaginatorAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns the number of results.
     *
     * @return int The number of results
     */
    public function getNbResults()
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
     *
     * @return array|\Traversable The slice
     */
    public function getSlice($offset, $length)
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
