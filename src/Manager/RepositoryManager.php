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
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Repository;
use Psr\Container\ContainerInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents
 */
class RepositoryManager implements RepositoryManagerInterface
{
    /**
     * @var array<string, array{finder: FinderInterface, repositoryName: ?class-string}>
     */
    private $indexes = [];

    /**
     * @var array<string, Repository>
     */
    private $repositories = [];

    private ContainerInterface $repositoryLocator;

    public function __construct(ContainerInterface $repositoryLocator)
    {
        $this->repositoryLocator = $repositoryLocator;
    }

    public function addIndex(string $indexName, FinderInterface $finder, ?string $repositoryName = null): void
    {
        $this->indexes[$indexName] = [
            'finder' => $finder,
            'repositoryName' => $repositoryName,
        ];
    }

    /**
     * Return repository for entity.
     *
     * Returns custom repository if one specified otherwise
     * returns a basic repository.
     */
    public function getRepository(string $indexName): Repository
    {
        if (isset($this->repositories[$indexName])) {
            return $this->repositories[$indexName];
        }

        if (!$this->hasRepository($indexName)) {
            throw new \RuntimeException(\sprintf('No repository is configured for index "%s"', $indexName));
        }

        $repository = $this->createRepository($indexName);
        $this->repositories[$indexName] = $repository;

        return $repository;
    }

    public function hasRepository(string $indexName): bool
    {
        return isset($this->indexes[$indexName]);
    }

    /**
     * @return class-string
     */
    protected function getRepositoryName(string $indexName): string
    {
        return $this->indexes[$indexName]['repositoryName'] ?? Repository::class;
    }

    /**
     * @return Repository
     */
    private function createRepository(string $indexName)
    {
        if ($this->repositoryLocator->has($indexName)) {
            return $this->repositoryLocator->get($indexName);
        }

        $finder = $this->indexes[$indexName]['finder'];

        if (!$finder instanceof PaginatedFinderInterface) {
            throw new \RuntimeException(\sprintf('Finder for index "%s" must implement PaginatedFinderInterface', $indexName));
        }

        return new Repository($finder);
    }
}
