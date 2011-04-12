<?php

namespace FOQ\ElasticaBundle\DependencyInjection\Compiler;

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
        if (!$container->hasDefinition('foq_elastica.populator')) {
            return;
        }

        $providers = array();
        foreach ($container->findTaggedServiceIds('foq_elastica.provider') as $id => $attributes) {
            $providers[] = new Reference($id);
        }

        $container->getDefinition('foq_elastica.populator')->setArgument(0, $providers);
    }
}
