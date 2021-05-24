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
class RepositoryManager implements RepositoryManagerInterface
{
    /**
     * @var array
     */
    private $indexes = [];

    /**
     * @var array
     */
    private $repositories = [];

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

    protected function getRepositoryName(string $indexName): string
    {
        return $this->indexes[$indexName]['repositoryName'] ?? Repository::class;
    }

    /**
     * @return mixed
     */
    private function createRepository(string $indexName)
    {
        if (!\class_exists($repositoryName = $this->getRepositoryName($indexName))) {
            throw new \RuntimeException(\sprintf('%s repository for index "%s" does not exist', $repositoryName, $indexName));
        }

        return new $repositoryName($this->indexes[$indexName]['finder']);
    }
}
