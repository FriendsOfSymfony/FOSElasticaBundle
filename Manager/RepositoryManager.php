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
use RuntimeException;

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
    private $types;

    /**
     * @var Repository[]
     */
    private $repositories;

    public function __construct()
    {
        $this->types = array();
        $this->repositories = array();
    }

    public function addType($indexTypeName, FinderInterface $finder, $repositoryName = null)
    {
        $this->types[$indexTypeName] = array(
            'finder' => $finder,
            'repositoryName' => $repositoryName,
        );
    }

    /**
     * Return repository for entity.
     *
     * Returns custom repository if one specified otherwise
     * returns a basic repository.
     *
     * @param string $typeName
     *
     * @return Repository
     */
    public function getRepository($typeName)
    {
        if (isset($this->repositories[$typeName])) {
            return $this->repositories[$typeName];
        }

        if (!isset($this->types[$typeName])) {
            throw new RuntimeException(sprintf('No search finder configured for %s', $typeName));
        }

        $repository = $this->createRepository($typeName);
        $this->repositories[$typeName] = $repository;

        return $repository;
    }

    /**
     * @param $typeName
     *
     * @return string
     */
    protected function getRepositoryName($typeName)
    {
        if (isset($this->types[$typeName]['repositoryName'])) {
            return $this->types[$typeName]['repositoryName'];
        }

        return 'FOS\ElasticaBundle\Repository';
    }

    /**
     * @param $typeName
     *
     * @return mixed
     */
    private function createRepository($typeName)
    {
        if (!class_exists($repositoryName = $this->getRepositoryName($typeName))) {
            throw new RuntimeException(sprintf('%s repository for %s does not exist', $repositoryName, $typeName));
        }

        return new $repositoryName($this->types[$typeName]['finder']);
    }
}
