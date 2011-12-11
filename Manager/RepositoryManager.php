<?php

namespace FOQ\ElasticaBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOQ\ElasticaBundle\Finder\FinderInterface;
use FOQ\ElasticaBundle\Repository;
use RuntimeException;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents.
 */
class RepositoryManager implements RepositoryManagerInterface
{
    protected $entities = array();
    protected $repositories = array();
    protected $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function addEntity($entityName, FinderInterface $finder, $repositoryName = null)
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
        $realEntityName = $entityName;
        if (strpos($entityName, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $entityName);
            $realEntityName = $this->managerRegistry->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        if (isset($this->repositories[$realEntityName])) {
            return $this->repositories[$realEntityName];
        }

        if (!isset($this->entities[$realEntityName])) {
            throw new RuntimeException(sprintf('No search finder configured for %s', $realEntityName));
        }

        if (isset($this->entities[$realEntityName]['repositoryName'])) {

            $repositoryName = $this->entities[$realEntityName]['repositoryName'];
            if (!class_exists($repositoryName)) {
                throw new RuntimeException(sprintf('%s repository for %s does not exist', $repositoryName, $realEntityName));
            }
            $repository = new $repositoryName($this->entities[$realEntityName]['finder']);
            $this->repositories[$realEntityName] = $repository;
            return $repository;
        }

        $repository = new Repository($this->entities[$realEntityName]['finder']);
        $this->repositories[$realEntityName] = $repository;
        return $repository;
    }

}
