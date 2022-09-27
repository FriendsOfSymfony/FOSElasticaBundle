<?php

declare(strict_types=1);

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FinderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('fos_elastica.finder') as $id => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['index'])) {
                    $container->setAlias(sprintf('fos_elastica.finder.%s', $tag['index']), $id);
                }
            }
        }
    }
}
