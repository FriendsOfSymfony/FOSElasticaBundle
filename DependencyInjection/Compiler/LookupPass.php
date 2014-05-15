<?php

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers any lookup services into the LookupManager
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class LookupPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.lookup_manager')) {
            return;
        }

        $lookups = array();
        foreach ($container->findTaggedServiceIds('fos_elastica.lookup') as $id => $tags) {
            $lookups[] = new Reference($id);
        }

        $managerDefinition = $container->getDefinition('fos_elastica.lookup_manager');
        $managerDefinition->setArguments(0, $lookups);
    }
}
