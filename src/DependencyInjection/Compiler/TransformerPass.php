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

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers Transformer implementations into the TransformerCollection.
 *
 * @author Tim Nagel <tim@nagel.com.au>
 */
class TransformerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.elastica_to_model_transformer.collection')) {
            return;
        }

        $transformers = [];

        foreach ($container->findTaggedServiceIds('fos_elastica.elastica_to_model_transformer') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['index']) || empty($tag['type'])) {
                    throw new InvalidArgumentException('The Transformer must have both a type and an index defined.');
                }

                $transformers[$tag['index']][$tag['type']] = new Reference($id);
            }
        }

        foreach ($transformers as $index => $indexTransformers) {
            if (!$container->hasDefinition(sprintf('fos_elastica.elastica_to_model_transformer.collection.%s', $index))) {
                continue;
            }

            $index = $container->getDefinition(sprintf('fos_elastica.elastica_to_model_transformer.collection.%s', $index));
            $index->replaceArgument(0, $indexTransformers);
        }
    }
}
