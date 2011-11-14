<?php

namespace FOQ\ElasticaBundle;

use RuntimeException;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents.
 */
class Manager
{
    protected $entities;
    protected $repositories;

    public function addEntity($entityName, $finder, $repositoryName = null)
    {
        $this->entities[$entityName]= array();
        $this->entities[$entityName]['finder'] = $finder;
        $this->entities[$entityName]['repositoryName'] = $repositoryName;
    }

    /**
     * Return repository for entity
     *
     * Returns custom repository if one specified otherwise
     * returns a basic respository.
     */
    public function getRepository($entityName)
    {
        if (isset($this->repositories[$entityName])) {
            return $this->repositories[$entityName];
        }

        if (!isset($this->entities[$entityName])) {
            throw new RuntimeException(sprintf('No search finder configured for %s', $entityName));
        }

        if (isset($this->entities[$entityName]['repositoryName'])) {

            $repositoryName = $this->entities[$entityName]['repositoryName'];
            if (!class_exists($repositoryName)) {
                throw new RuntimeException(sprintf('%s repository for %s does not exist', $repositoryName, $entityName));
            }
            $repository = new $repositoryName($this->entities[$entityName]['finder']);
            $this->repositories[$entityName] = $repository;
            return $repository;
        }

        $repository = new Repository($this->entities[$entityName]['finder']);
        $this->repositories[$entityName] = $repository;
        return $repository;
    }

}
