<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use FOS\ElasticaBundle\Persister\PagerPersisterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterPagerPersistersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.pager_persister_registry')) {
            return;
        }

        $registry = $container->getDefinition('fos_elastica.pager_persister_registry');

        $nameToServiceIdMap = [];
        foreach ($container->findTaggedServiceIds('fos_elastica.pager_persister', true) as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['persisterName'])) {
                    throw new \InvalidArgumentException(sprintf('Elastica pager persister "%s" must specify the "persisterName" attribute.', $id));
                }

                $persisterName = $attribute['persisterName'];

                if (isset($nameToServiceIdMap[$persisterName])) {
                    throw new \InvalidArgumentException(sprintf('Cannot register pager persister "%s". The pager persister "%s" has been registered for same name "%s"', $id, $nameToServiceIdMap[$persisterName], $persisterName));
                }

                $persisterDef = $container->getDefinition($id);
                if (!$persisterDef->getFactory() && $persisterDef->getClass()) {
                    // You are on your own if you use a factory to create a persister.
                    // It would fail in runtime if the factory does not return a proper persister.
                    $this->assertClassImplementsPagerPersisterInterface($id, $container->getParameterBag()->resolveValue($persisterDef->getClass()));
                }

                if (!$persisterDef->isPublic()) {
                    throw new \InvalidArgumentException(sprintf('Elastica pager persister "%s" must be a public service', $id));
                }

                $nameToServiceIdMap[$persisterName] = $id;
            }
        }

        $registry->replaceArgument(0, $nameToServiceIdMap);
    }

    /**
     * @param $persisterId
     * @param $persisterClass
     *
     * @throws \InvalidArgumentException if persister service does not implement ObjectPersisterInterface
     */
    private function assertClassImplementsPagerPersisterInterface($persisterId, $persisterClass)
    {
        $rc = new \ReflectionClass($persisterClass);

        if (!$rc->implementsInterface(PagerPersisterInterface::class)) {
            throw new \InvalidArgumentException(sprintf('Elastica pager persister "%s" with class "%s" must implement "%s".', $persisterId, $persisterClass, PagerPersisterInterface::class));
        }
    }
}
