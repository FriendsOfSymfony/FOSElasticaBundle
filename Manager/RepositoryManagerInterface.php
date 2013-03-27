<?php

namespace FOS\ElasticaBundle\Manager;

use FOS\ElasticaBundle\Finder\FinderInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents.
 */
interface RepositoryManagerInterface
{

    /**
     * Adds entity name and its finder.
     * Custom repository class name can also be added.
     *
     * @param string $entityName
     * @param        $finder
     * @param string $repositoryName
     */
    public function addEntity($entityName, FinderInterface $finder, $repositoryName = null);

    /**
     * Return repository for entity
     *
     * Returns custom repository if one specified otherwise
     * returns a basic repository.
     *
     * @param string $entityName
     */
    public function getRepository($entityName);

}
