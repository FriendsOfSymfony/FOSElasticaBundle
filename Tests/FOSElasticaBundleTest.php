<?php

namespace FOS\ElasticaBundle\Tests\Resetter;

use FOS\ElasticaBundle\FOSElasticaBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class FOSElasticaBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testCompilerPassesAreRegistered()
    {
        $container = $this
            ->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container
            ->expects($this->atLeastOnce())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Symfony\\Component\\DependencyInjection\\Compiler\\CompilerPassInterface'));


        $bundle = new FOSElasticaBundle();
        $bundle->build($container);
    }
}
