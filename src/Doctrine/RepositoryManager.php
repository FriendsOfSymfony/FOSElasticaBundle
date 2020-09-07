<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents
 */
class RepositoryManager implements RepositoryManagerInterface
{
    /** @var array */
    protected $entities = [];

    /** @var array */
    protected $repositories = [];

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @var RepositoryManagerInterface
     */
    private $repositoryManager;

    /**
     * @param ManagerRegistry            $managerRegistry
     * @param RepositoryManagerInterface $repositoryManager
     */
    public function __construct(ManagerRegistry $managerRegistry, RepositoryManagerInterface $repositoryManager)
    {
        $this->managerRegistry = $managerRegistry;
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * {@inheritdoc}
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
        if (false !== strpos($entityName, ':')) {
            list($namespaceAlias, $simpleClassName) = explode(':', $entityName);
            $realEntityName = $this->managerRegistry->getAliasNamespace($namespaceAlias).'\\'.$simpleClassName;
        }

        if (isset($this->entities[$realEntityName])) {
            $realEntityName = $this->entities[$realEntityName];
        }

        return $this->repositoryManager->getRepository($realEntityName);
    }

    public function hasRepository($typeName): bool
    {
        return $this->repositoryManager->hasRepository($typeName);
    }
}
