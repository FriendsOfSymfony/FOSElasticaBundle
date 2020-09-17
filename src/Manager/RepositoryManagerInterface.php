<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Manager;

use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\Repository;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents
 */
interface RepositoryManagerInterface
{
    /**
     * Adds index name and its finder.
     * Custom repository class name can also be added.
     */
    public function addIndex(string $indexName, FinderInterface $finder, ?string $repositoryName = null): void;

    /**
     * Return repository for entity.
     *
     * Returns custom repository if one specified otherwise
     * returns a basic repository.
     */
    public function getRepository(string $indexName): Repository;

    /**
     * Check whether a repository exists for the index.
     */
    public function hasRepository(string $indexName): bool;
}
