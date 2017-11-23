<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterPersistersPass implements CompilerPassInterface
{
    /**
     * Mapping of class names to booleans indicating whether the class
     * implements ObjectPersisterInterface.
     *
     * @var array
     */
    private $implementations = [];

    /**
     * @see Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.persister_registry')) {
            return;
        }

        // Infer the default index name from the service alias
        $defaultIndex = substr($container->getAlias('fos_elastica.index'), 19);

        $registry = $container->getDefinition('fos_elastica.persister_registry');
        $persisters = $container->findTaggedServiceIds('fos_elastica.persister');

        $persistersByPriority = [];
        foreach ($persisters as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $persistersByPriority[$priority][$id] = $attributes;
        }

        if (!empty($persistersByPriority)) {
            krsort($persistersByPriority);
            $persistersByPriority = call_user_func_array('array_merge', $persistersByPriority);
        }

        foreach ($persistersByPriority as $persisterId => $tags) {
            $index = $type = null;
            $class = $container->getDefinition($persisterId)->getClass();

            if (!$class || !$this->isPersisterImplementation($class)) {
                throw new \InvalidArgumentException(sprintf('Elastica persister "%s" with class "%s" must implement PersisterInterface.', $persisterId, $class));
            }

            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \InvalidArgumentException(sprintf('Elastica persister "%s" must specify the "type" attribute.', $persisterId));
                }

                $index = isset($tag['index']) ? $tag['index'] : $defaultIndex;
                $type = $tag['type'];
            }

            $registry->addMethodCall('addPersister', [$index, $type, $persisterId]);
        }
    }

    /**
     * Returns whether the class implements PersisterInterface.
     *
     * @param string $class
     *
     * @return bool
     */
    private function isPersisterImplementation($class)
    {
        if (!isset($this->implementations[$class])) {
            $refl = new \ReflectionClass($class);
            $this->implementations[$class] = $refl->implementsInterface('FOS\ElasticaBundle\Persister\ObjectPersisterInterface');
        }

        return $this->implementations[$class];
    }
}