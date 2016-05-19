<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Manager\RepositoryManager as BaseManager;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Allows retrieval of basic or custom repository for mapped Doctrine
 * entities/documents.
 */
class RepositoryManager extends BaseManager
{
    /** @var array */
    protected $entities = array();
    
    /** @var array */
    protected $repositories = array();
    
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param Reader          $reader
     */
    public function __construct(ManagerRegistry $managerRegistry, Reader $reader)
    {
        $this->managerRegistry = $managerRegistry;
        parent::__construct($reader);
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

        return parent::getRepository($realEntityName);
    }
}
