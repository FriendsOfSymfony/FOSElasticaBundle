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
use FOS\ElasticaBundle\Repository;

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

    public function __construct(ManagerRegistry $managerRegistry, RepositoryManagerInterface $repositoryManager)
    {
        $this->managerRegistry = $managerRegistry;
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addIndex(string $indexName, FinderInterface $finder, ?string $repositoryName = null): void
    {
        throw new \LogicException(__METHOD__.' should not be called. Call addIndex on the main repository manager');
    }

    public function addEntity($entityName, $indexName)
    {
        $this->entities[$entityName] = $indexName;
    }

    /**
     * Returns custom repository if one specified otherwise returns a basic repository.
     *
     * {@inheritdoc}
     */
    public function getRepository(string $entityName): Repository
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

    public function hasRepository(string $indexName): bool
    {
        return $this->repositoryManager->hasRepository($indexName);
    }
}
