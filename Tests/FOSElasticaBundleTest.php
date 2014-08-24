<?php

namespace FOS\ElasticaBundle\Tests\Resetter;

use FOS\ElasticaBundle\FOSElasticaBundle;

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
