<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
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
    protected array $entities = [];
    protected array $repositories = [];
    protected ManagerRegistry $managerRegistry;
    private RepositoryManagerInterface $repositoryManager;

    public function __construct(ManagerRegistry $managerRegistry, RepositoryManagerInterface $repositoryManager)
    {
        $this->managerRegistry = $managerRegistry;
        $this->repositoryManager = $repositoryManager;
    }

    public function addIndex(string $indexName, FinderInterface $finder, ?string $repositoryName = null): void
    {
        throw new \LogicException(__METHOD__.' should not be called. Call addIndex on the main repository manager');
    }

    public function addEntity($entityName, $indexName): void
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
        if (false !== \strpos($entityName, ':')) {
            [$namespaceAlias, $simpleClassName] = \explode(':', $entityName);
            // @link https://github.com/doctrine/persistence/pull/204
            if (\method_exists($this->managerRegistry, 'getAliasNamespace')) {
                $realEntityName = $this->managerRegistry->getAliasNamespace($namespaceAlias).'\\'.$simpleClassName;
            } else {
                $realEntityName = $simpleClassName.'::class';
            }
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
