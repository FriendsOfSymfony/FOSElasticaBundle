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

/**
 * @phpstan-import-type TQuery from FinderInterface
 * @phpstan-import-type TOptions from FinderInterface
 */
interface HybridFinderInterface
{
    /**
     * Searches for query hybrid results within a given limit.
     *
     * @param TQuery   $query
     * @param TOptions $options
     *
     * @return HybridResult[]
     */
    public function findHybrid($query, ?int $limit = null, array $options = []);
}
