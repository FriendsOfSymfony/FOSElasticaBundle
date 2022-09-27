<?php

declare(strict_types=1);

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('fos_elastica.pager_provider') as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['index'])) {
                    $container->setAlias(sprintf('fos_elastica.pager_provider.%s', $tag['index']), $id);
                }
            }
        }
    }
}
