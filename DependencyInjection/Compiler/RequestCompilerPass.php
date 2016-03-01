<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

final class RequestCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.paginator.subscriber')) {
            return;
        }

        $definition = $container->getDefinition('fos_elastica.paginator.subscriber');
        if ($container->hasDefinition('request_stack')) {
            $arguments = array(new Reference('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE, false));
        } else {
            $arguments = array(new Reference('request', ContainerInterface::NULL_ON_INVALID_REFERENCE, false));
        }

        $definition->addMethodCall('setRequest', $arguments);
    }
}
