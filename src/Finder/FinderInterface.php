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

use Elastica\Collapse;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Suggest;
use Elastica\Suggest\AbstractSuggest;

/**
 * @phpstan-type TQuery = Query|AbstractSuggest|AbstractQuery|Suggest|Collapse|array<string, mixed>|string
 *
 * @see \Elastica\Query::create()
 */
interface FinderInterface
{
    /**
     * Searches for query results within a given limit.
     *
     * @param mixed $query Can be a string, an array or an \Elastica\Query object
     * @phpstan-param TQuery $query
     *
     * @param int|null $limit How many results to get
     *
     * @return array results
     */
    public function find($query, ?int $limit = null, array $options = []);
}
