<?php

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AddProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.populator')) {
            return;
        }

        $providers = array();
        foreach ($container->findTaggedServiceIds('fos_elastica.provider') as $id => $attributes) {
            $providers[$id] = new Reference($id);
        }

        $container->getDefinition('fos_elastica.populator')->replaceArgument(0, $providers);
    }
}
