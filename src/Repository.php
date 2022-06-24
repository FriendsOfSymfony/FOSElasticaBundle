<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle;

use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Basic repository to be extended to hold custom queries to be run
 * in the finder
 *
 * @phpstan-import-type TQuery from FinderInterface
 * @phpstan-import-type TOptions from FinderInterface
 */
class Repository
{
    /** @var PaginatedFinderInterface */
    protected $finder;

    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param mixed $query
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return array<object>
     */
    public function find($query, ?int $limit = null, array $options = [])
    {
        return $this->finder->find($query, $limit, $options);
    }

    /**
     * @param mixed $query
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return list<HybridResult>
     */
    public function findHybrid($query, ?int $limit = null, array $options = [])
    {
        return $this->finder->findHybrid($query, $limit, $options);
    }

    /**
     * @param mixed $query
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return \Pagerfanta\Pagerfanta<object>
     */
    public function findPaginated($query, array $options = [])
    {
        return $this->finder->findPaginated($query, $options);
    }

    /**
     * @param mixed $query
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return Paginator\PaginatorAdapterInterface
     */
    public function createPaginatorAdapter($query, array $options = [])
    {
        return $this->finder->createPaginatorAdapter($query, $options);
    }

    /**
     * @param mixed $query
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return Paginator\PaginatorAdapterInterface
     */
    public function createHybridPaginatorAdapter($query, array $options = [])
    {
        return $this->finder->createHybridPaginatorAdapter($query, $options);
    }
}
