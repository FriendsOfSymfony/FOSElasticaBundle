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

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterPersistersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.persister_registry')) {
            return;
        }

        $defaultIndex = $container->getParameter('fos_elastica.default_index');
        $registry = $container->getDefinition('fos_elastica.persister_registry');

        $registeredPersisters = [];
        foreach ($container->findTaggedServiceIds('fos_elastica.persister') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['type'])) {
                    throw new \InvalidArgumentException(sprintf('Elastica persister "%s" must specify the "type" attribute.', $id));
                }

                $index = isset($attribute['index']) ? $attribute['index'] : $defaultIndex;
                $type = $attribute['type'];

                if (isset($registeredPersisters[$index][$type])) {
                    throw new \InvalidArgumentException(sprintf(
                        'Cannot register persister "%s". The persister "%s" has been registered for same index "%s" and type "%s"',
                        $id,
                        $registeredPersisters[$index][$type],
                        $index,
                        $type
                    ));
                }

                $persisterDef = $container->getDefinition($id);
                if (!$persisterDef->getFactory()) {
                    // You are on your own if you use a factory to create a persister.
                    // It would fail in runtime if the factory does not return a proper persister.
                    $this->assertClassImplementsPersisterInterface($id, $persisterDef->getClass());
                }

                $registeredPersisters[$index][$type] = $id;
            }
        }

        $registry->replaceArgument(0, $registeredPersisters);
    }

    /**
     * @param $persisterId
     * @param $persisterClass
     *
     * @throws \InvalidArgumentException if persister service does not implement ObjectPersisterInterface
     */
    private function assertClassImplementsPersisterInterface($persisterId, $persisterClass)
    {
        $rc = new \ReflectionClass($persisterClass);

        if (!$rc->implementsInterface(ObjectPersisterInterface::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Elastica persister "%s" with class "%s" must implement "%s".',
                $persisterId,
                $persisterClass,
                ObjectPersisterInterface::class
            ));
        }
    }


//
//        if (!$container->hasDefinition('fos_elastica.persister_registry')) {
//            return;
//        }
//
//        $defaultIndex = $container->getParameter('fos_elastica.default_index');
//
//        $registry = $container->getDefinition('fos_elastica.persister_registry');
//        $persisters = $container->findTaggedServiceIds('fos_elastica.persister');
//
//        $persistersByPriority = [];
//        foreach ($persisters as $id => $attributes) {
//            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
//            $persistersByPriority[$priority][$id] = $attributes;
//        }
//
//        if (!empty($persistersByPriority)) {
//            krsort($persistersByPriority);
//            $persistersByPriority = call_user_func_array('array_merge', $persistersByPriority);
//        }
//
//        foreach ($persistersByPriority as $persisterId => $tags) {
//            $index = $type = null;
//            $class = $container->getDefinition($persisterId)->getClass();
//
//            if (!$class || !$this->isPersisterImplementation($class)) {
//                throw new \InvalidArgumentException(sprintf('Elastica persister "%s" with class "%s" must implement PersisterInterface.', $persisterId, $class));
//            }
//
//            foreach ($tags as $tag) {
//                if (!isset($tag['type'])) {
//                    throw new \InvalidArgumentException(sprintf('Elastica persister "%s" must specify the "type" attribute.', $persisterId));
//                }
//
//                $index = isset($tag['index']) ? $tag['index'] : $defaultIndex;
//                $type = $tag['type'];
//            }
//
//            $registry->addMethodCall('addPersister', [$index, $type, $persisterId]);
//        }
//    }
//
//    /**
//     * Returns whether the class implements PersisterInterface.
//     *
//     * @param string $class
//     *
//     * @return bool
//     */
//    private function isPersisterImplementation($class)
//    {
//        if (!isset($this->implementations[$class])) {
//            $refl = new \ReflectionClass($class);
//            $this->implementations[$class] = $refl->implementsInterface('FOS\ElasticaBundle\Persister\ObjectPersisterInterface');
//        }
//
//        return $this->implementations[$class];
//    }
}