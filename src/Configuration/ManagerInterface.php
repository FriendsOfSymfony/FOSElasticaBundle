<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

/**
 * Central manager for index and type configuration.
 */
interface ManagerInterface
{
    /**
     * Returns configuration for an index.
     *
     * @throws \InvalidArgumentException if no index configured for the given name
     */
    public function getIndexConfiguration(string $index): IndexConfigInterface;

    /**
     * Returns an array of known index names.
     *
     * @phpstan-return list<string>
     */
    public function getIndexNames(): array;
}
