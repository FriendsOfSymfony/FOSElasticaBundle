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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConfigSourcePass implements CompilerPassInterface
{
    public const SOURCE_TYPE_INDEX_TEMPLATE = 'index_template';

    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.config_manager')) {
            return;
        }

        $indexSources = [];
        $indexTemplateSources = [];
        foreach (\array_keys($container->findTaggedServiceIds('fos_elastica.config_source')) as $id) {
            $tag = $container->findDefinition($id)->getTag('fos_elastica.config_source');
            if (isset($tag[0]['source']) && self::SOURCE_TYPE_INDEX_TEMPLATE === $tag[0]['source']) {
                $indexTemplateSources[] = new Reference($id);
            } else {
                $indexSources[] = new Reference($id);
            }
        }

        $container->getDefinition('fos_elastica.config_manager')->replaceArgument(0, $indexSources);
        $container->getDefinition('fos_elastica.config_manager.index_templates')->replaceArgument(0, $indexTemplateSources);
    }
}
