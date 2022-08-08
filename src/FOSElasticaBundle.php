<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle;

use FOS\ElasticaBundle\DependencyInjection\Compiler\ConfigSourcePass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\IndexPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FOSElasticaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigSourcePass());
        $container->addCompilerPass(new IndexPass());
    }
}
