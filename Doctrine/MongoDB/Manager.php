<?php

namespace FOQ\ElasticaBundle\Doctrine\MongoDB;

use FOQ\ElasticaBundle\Finder\FinderInterface;
use FOQ\ElasticaBundle\Manager\ManagerInterface;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Decorates Manager to add Doctrine style BundleAliasName:DocumentName syntax to repository retrieval.
 */
class Manager implements ManagerInterface
{
    protected $dm;
    protected $manager;

    public function __construct(ObjectManager $dm, ManagerInterface $manager)
    {
        $this->dm = $dm;
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
            $realClassName = $this->dm->getConfiguration()->getDocumentNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }
        return $this->manager->getRepository($realClassName);
    }

}