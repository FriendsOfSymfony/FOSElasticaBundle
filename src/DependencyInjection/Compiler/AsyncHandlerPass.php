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

class AsyncHandlerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.async_parsist_page_handler')) {
            return;
        }

        $messageBus = $container->getParameter('message_bus') ?? 'messenger.default_bus';

        $container->getDefinition('fos_elastica.async_parsist_page_handler')
            ->addTag('messenger.message_handler', ['bus' => $messageBus]);
    }
}
