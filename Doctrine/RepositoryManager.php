<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManager as BaseManager;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents.
 *
 * @deprecated
 */
class RepositoryManager implements RepositoryManagerInterface
{
    /** @var array */
    protected $entities = array();
    
    /** @var array */
    protected $repositories = array();
    
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @var RepositoryManagerInterface
     */
    private $repositoryManager;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param RepositoryManagerInterface $repositoryManager
     */
    public function __construct(ManagerRegistry $managerRegistry, RepositoryManagerInterface $repositoryManager)
    {
        $this->managerRegistry = $managerRegistry;
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * @inheritDoc
     */
    public function addType($indexTypeName, FinderInterface $finder, $repositoryName = null)
    {
        throw new \LogicException(__METHOD__.' should not be called. Call addType on the main repository manager');
    }

    public function addEntity($entityName, $indexTypeName)
    {
        $this->entities[$entityName] = $indexTypeName;
    }

    /**
     * Returns custom repository if one specified otherwise returns a basic repository.
     *
     * {@inheritdoc}
     */
    public function getRepository($entityName)
    {
        $realEntityName = $entityName;
        if (strpos($entityName, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $entityName);
            $realEntityName = $this->managerRegistry->getAliasNamespace($namespaceAlias).'\\'.$simpleClassName;
        }

        if (isset($this->entities[$realEntityName])) {
            $realEntityName = $this->entities[$realEntityName];
        }

        return $this->repositoryManager->getRepository($realEntityName);
    }
}
