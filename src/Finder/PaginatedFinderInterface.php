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

use FOS\ElasticaBundle\HybridResult;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * @phpstan-import-type TQuery from FinderInterface
 *
 * @method Pagerfanta findHybridPaginated(mixed $query) Searches for query hybrid results.
 *
 * @phpstan-method Pagerfanta<HybridResult> findHybridPaginated(TQuery $query)
 * @phpstan-method HybridResult[] findHybrid(TQuery $query, ?int $limit = null, array $options = [])
 *
 * @phpstan-import-type TOptions from FinderInterface
 */
interface PaginatedFinderInterface extends FinderInterface
{
    /**
     * Searches for query results and returns them wrapped in a paginator.
     *
     * @param mixed $query Can be a string, an array or an \Elastica\Query object
     *
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return Pagerfanta<object> paginated results
     */
    public function findPaginated($query, array $options = []);

    /**
     * Creates a paginator adapter for this query.
     *
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return PaginatorAdapterInterface
     */
    public function createPaginatorAdapter($query, array $options = []);

    /**
     * Creates a hybrid paginator adapter for this query.
     *
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return PaginatorAdapterInterface
     */
    public function createHybridPaginatorAdapter($query, array $options = []);

    /**
     * Creates a raw paginator adapter for this query.
     *
     * @phpstan-param TQuery $query
     * @phpstan-param TOptions $options
     *
     * @return PaginatorAdapterInterface
     */
    public function createRawPaginatorAdapter($query, array $options = []);
}
