<?php

namespace FOS\ElasticaBundle;

use FOS\ElasticaBundle\DependencyInjection\Compiler\ConfigSourcePass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\IndexPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPagerPersistersPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterProvidersPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\TransformerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPagerProvidersPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterPersistersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
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
        $container->addCompilerPass(new RegisterProvidersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new RegisterPagerProvidersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new RegisterPersistersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new RegisterPagerPersistersPass());
        $container->addCompilerPass(new TransformerPass());
    }
}
