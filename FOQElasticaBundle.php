<?php

namespace FOQ\ElasticaBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use FOQ\ElasticaBundle\DependencyInjection\Compiler\AddProviderPass;
use FOQ\ElasticaBundle\DependencyInjection\Compiler\TransformerPass;

class FOQElasticaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddProviderPass());
        $container->addCompilerPass(new TransformerPass());
    }
}
