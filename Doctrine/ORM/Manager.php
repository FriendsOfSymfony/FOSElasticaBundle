<?php

namespace FOQ\ElasticaBundle\Doctrine\ORM;

use FOQ\ElasticaBundle\Finder\FinderInterface;
use FOQ\ElasticaBundle\Manager\ManagerInterface;
use Doctrine\ORM\EntityManager;
/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Decorates Manager to add Doctrine style BundleAliasName:EntityName syntax to repository retrieval.
 */
class Manager implements ManagerInterface
{
    protected $em;
    protected $manager;

    public function __construct(EntityManager $em, ManagerInterface $manager)
    {
        $this->em = $em;
        $this->manager = $manager;
    }

    public function addEntity($entityName, FinderInterface $finder, $repositoryName = null)
    {
        $this->manager->addEntity($entityName, $finder, $repositoryName);
    }

    public function getRepository($className)
    {
        $realClassName = $className;

        // Check for namespace alias
        if (strpos($className, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $className);
            $realClassName = $this->em->getConfiguration()->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }
        return $this->manager->getRepository($realClassName);
    }

}
