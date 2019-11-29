<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Basic repository to be extended to hold custom queries to be run
 * in the finder
 */
class Repository
{
    /** @var PaginatedFinderInterface */
    protected $finder;

    /**
     * @param PaginatedFinderInterface $finder
     */
    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param mixed $query
     * @param int   $limit
     * @param array $options
     *
     * @return array
     */
    public function find($query, $limit = null, $options = [])
    {
        return $this->finder->find($query, $limit, $options);
    }

    /**
     * @param mixed $query
     * @param int   $limit
     * @param array $options
     *
     * @return mixed
     */
    public function findHybrid($query, $limit = null, $options = [])
    {
        return $this->finder->findHybrid($query, $limit, $options);
    }

    /**
     * @param mixed $query
     * @param array $options
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function findPaginated($query, $options = [])
    {
        return $this->finder->findPaginated($query, $options);
    }

    /**
     * @param mixed $query
     * @param array  $options
     *
     * @return Paginator\PaginatorAdapterInterface
     */
    public function createPaginatorAdapter($query, $options = [])
    {
        return $this->finder->createPaginatorAdapter($query, $options);
    }

    /**
     * @param mixed $query
     *
     * @return Paginator\HybridPaginatorAdapter
     */
    public function createHybridPaginatorAdapter($query)
    {
        return $this->finder->createHybridPaginatorAdapter($query);
    }
}
