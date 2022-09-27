<?php

declare(strict_types=1);

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('fos_elastica.pager_provider') as $id => $tags) {
            foreach ($tags as $tag) {
                $container->set(sprintf('fos_elastica.pager_provider.%s', $tag['name']), new Reference($id));
            }
        }
    }
}
