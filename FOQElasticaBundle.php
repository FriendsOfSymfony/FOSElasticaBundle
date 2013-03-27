<?php

namespace FOS\ElasticaBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use FOS\ElasticaBundle\DependencyInjection\Compiler\AddProviderPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\TransformerPass;

class FOSElasticaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddProviderPass());
        $container->addCompilerPass(new TransformerPass());
    }
}
