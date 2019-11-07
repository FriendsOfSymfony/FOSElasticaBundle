<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
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
     */
    public function getIndexConfiguration(string $index): IndexConfigInterface;

    /**
     * Returns an array of known index names.
     */
    public function getIndexNames(): array;

    /**
     * Returns a type configuration.
     */
    public function getTypeConfiguration(string $index, string $type): TypeConfig;
}
