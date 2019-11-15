<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
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
     * Adds type name and its finder.
     * Custom repository class name can also be added.
     *
     * @param string $indexTypeName  The type name in "index/type" format
     * @param        $finder
     * @param string $repositoryName
     */
    public function addType($indexTypeName, FinderInterface $finder, $repositoryName = null);

    /**
     * Return repository for entity.
     *
     * Returns custom repository if one specified otherwise
     * returns a basic repository.
     *
     * @param $typeName
     *
     * @return Repository
     */
    public function getRepository($typeName);

    /**
     * Checks if repository exists for type
     *
     * @param $typeName
     * @return bool
     */
    public function hasRepository($typeName): bool;
}
