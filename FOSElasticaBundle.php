<?php

namespace FOS\ElasticaBundle;

use FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterProvidersPass;
use FOS\ElasticaBundle\DependencyInjection\Compiler\TransformerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle.
 *
 */
class FOSElasticaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterProvidersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new TransformerPass());
    }
}
