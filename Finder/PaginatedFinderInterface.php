<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Finder;

use Elastica\Query;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;

interface PaginatedFinderInterface extends FinderInterface
{
    /**
     * Searches for query results and returns them wrapped in a paginator.
     *
     * @param mixed $query   Can be a string, an array or an \Elastica\Query object
     * @param array $options
     *
     * @return Pagerfanta paginated results
     */
    public function findPaginated($query, $options = []);

    /**
     * Creates a paginator adapter for this query.
     *
     * @param mixed $query
     * @param array $options
     *
     * @return PaginatorAdapterInterface
     */
    public function createPaginatorAdapter($query, $options = []);

    /**
     * Creates a hybrid paginator adapter for this query.
     *
     * @param mixed $query
     *
     * @return PaginatorAdapterInterface
     */
    public function createHybridPaginatorAdapter($query);

    /**
     * Creates a raw paginator adapter for this query.
     *
     * @param mixed $query
     * @param array $options
     *
     * @return PaginatorAdapterInterface
     */
    public function createRawPaginatorAdapter($query, $options = []);
}
