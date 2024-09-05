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
use Pagerfanta\PagerfantaInterface;
use Traversable;

if (!method_exists(PagerfantaInterface::class, 'getAdapter')) {
    class_alias(LegacyFantaPaginatorAdapterTrait::class, FantaPaginatorAdapterTrait::class);
}

class FantaPaginatorAdapter implements AdapterInterface
{
    use FantaPaginatorAdapterTrait;

    private $adapter;

    /**
     * @param \FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface $adapter
     */
    public function __construct(PaginatorAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
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
