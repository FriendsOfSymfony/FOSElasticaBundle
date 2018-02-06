<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle;

use FOS\ElasticaBundle\DependencyInjection\Compiler\ConfigSourcePass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\IndexPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPagerPersistersPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\TransformerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPagerProvidersPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPersistersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FOSElasticaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigSourcePass());
        $container->addCompilerPass(new IndexPass());
        $container->addCompilerPass(new RegisterPagerProvidersPass());
        $container->addCompilerPass(new RegisterPersistersPass());
        $container->addCompilerPass(new RegisterPagerPersistersPass());
        $container->addCompilerPass(new TransformerPass());
    }
}
