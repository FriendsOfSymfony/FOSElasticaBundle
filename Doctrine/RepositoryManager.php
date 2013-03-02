<?php

namespace FOQ\ElasticaBundle\Doctrine;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use FOQ\ElasticaBundle\Manager\RepositoryManager as BaseManager;

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

    public function __construct(ManagerRegistry $managerRegistry, Reader $reader)
    {
        $this->managerRegistry = $managerRegistry;
        parent::__construct($reader);
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

}
