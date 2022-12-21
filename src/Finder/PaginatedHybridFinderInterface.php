<?php

declare(strict_types=1);

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
use Pagerfanta\PagerfantaInterface;

/**
 * @phpstan-import-type TQuery from FinderInterface
 * @phpstan-import-type TOptions from FinderInterface
 */
interface PaginatedHybridFinderInterface extends HybridFinderInterface
{
    /**
     * Searches for query hybrid results and returns them wrapped in a paginator.
     *
     * @param TQuery $query
     *
     * @return PagerfantaInterface<HybridResult>
     */
    public function findHybridPaginated($query);

    /**
     * Creates a hybrid paginator adapter for this query.
     *
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return PaginatorAdapterInterface
     */
    public function createHybridPaginatorAdapter($query, array $options = []);
}
