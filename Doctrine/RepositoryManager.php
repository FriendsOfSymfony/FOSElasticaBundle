<?php

namespace FOQ\ElasticaBundle\Doctrine;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use FOQ\ElasticaBundle\Finder\FinderInterface;
use FOQ\ElasticaBundle\Manager\RepositoryManager as BaseManager;
use FOQ\ElasticaBundle\Repository;
use RuntimeException;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents.
 */
class RepositoryManager extends BaseManager
{
    protected $entities = array();
    protected $repositories = array();
    protected $managerRegistry;
    protected $reader;

    public function __construct(ManagerRegistry $managerRegistry, Reader $reader)
    {
        $this->managerRegistry = $managerRegistry;
        $this->reader          = $reader;
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

        return parent::getRepository($realEntityName);
    }

    protected function getCustomRepositoryName($realEntityName)
    {
        if (isset($this->entities[$realEntityName]['repositoryName'])) {
            return $this->entities[$realEntityName]['repositoryName'];
        }

        $refClass   = new \ReflectionClass($realEntityName);
        $annotation = $this->reader->getClassAnnotation($refClass, 'FOQ\\ElasticaBundle\\Configuration\\Search');
        if ($annotation) {
            $this->entities[$realEntityName]['repositoryName']
                = $annotation->repositoryClass;
            return $annotation->repositoryClass;
        }
    }

}
