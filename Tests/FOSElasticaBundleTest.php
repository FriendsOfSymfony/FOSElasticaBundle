<?php

namespace FOS\ElasticaBundle\Tests\Resetter;

use FOS\ElasticaBundle\FOSElasticaBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class FOSElasticaBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testCompilerPassesAreRegistered()
    {
        $passes = array(
            array (
                'FOS\ElasticaBundle\DependencyInjection\Compiler\RegisterProvidersPass',
                PassConfig::TYPE_BEFORE_REMOVING
            ),
            array (
                'FOS\ElasticaBundle\DependencyInjection\Compiler\TransformerPass'
            ),
        );

        $container = $this
            ->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container
            ->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf($passes[0][0]), $passes[0][1]);

        $container
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf($passes[1][0]));

        $bundle = new FOSElasticaBundle();

        $bundle->build($container);
    }
}
