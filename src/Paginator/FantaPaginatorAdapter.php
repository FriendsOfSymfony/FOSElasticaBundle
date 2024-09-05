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
use Traversable;

if (PHP_VERSION_ID < 80000) {
    class_alias(LegacyGetSliceTrait::class, GetSliceTrait::class);
}

class FantaPaginatorAdapter implements AdapterInterface
{
    use GetSliceTrait;

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
     * {@inheritdoc}
     */
    public function getMaxScore()
    {
        return $this->adapter->getMaxScore();
    }
}
