<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Finder;

use Elastica\Query;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * @method Pagerfanta findHybridPaginated($query) Searches for query hybrid results.
 */
interface PaginatedFinderInterface extends FinderInterface
{
    /**
     * Searches for query results and returns them wrapped in a paginator.
     *
     * @param mixed $query Can be a string, an array or an \Elastica\Query object
     *
     * @return Pagerfanta paginated results
     */
    public function findPaginated($query, array $options = []);

    /**
     * Creates a paginator adapter for this query.
     *
     * @param mixed $query
     *
     * @return PaginatorAdapterInterface
     */
    public function createPaginatorAdapter($query, array $options = []);

    /**
     * Creates a hybrid paginator adapter for this query.
     *
     * @param mixed $query
     *
     * @return PaginatorAdapterInterface
     */
    public function createHybridPaginatorAdapter($query, array $options = []);

    /**
     * Creates a raw paginator adapter for this query.
     *
     * @param mixed $query
     *
     * @return PaginatorAdapterInterface
     */
    public function createRawPaginatorAdapter($query, array $options = []);
}
