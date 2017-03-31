<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\DependencyInjection;

use FOS\ElasticaBundle\DependencyInjection\FOSElasticaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class FOSElasticaExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAddParentParamToObjectPersisterCall()
    {
        $config = Yaml::parse(file_get_contents(__DIR__.'/fixtures/config.yml'));

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();

        $extension->load($config, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('fos_elastica.object_persister.test_index.child_field'));

        $persisterCallDefinition = $containerBuilder->getDefinition('fos_elastica.object_persister.test_index.child_field');

        $arguments = $persisterCallDefinition->getArguments();
        $arguments = $arguments['index_3'];

        $this->assertArrayHasKey('_parent', $arguments);
        $this->assertSame('parent_field', $arguments['_parent']['type']);
    }

    public function testExtensionSupportsDriverlessTypePersistence()
    {
        $config = Yaml::parse(file_get_contents(__DIR__.'/fixtures/driverless_type.yml'));

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', true);

        $extension = new FOSElasticaExtension();
        $extension->load($config, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('fos_elastica.index.test_index'));
        $this->assertTrue($containerBuilder->hasDefinition('fos_elastica.index.test_index.driverless'));
        $this->assertFalse($containerBuilder->hasDefinition('fos_elastica.elastica_to_model_transformer.test_index.driverless'));
        $this->assertFalse($containerBuilder->hasDefinition('fos_elastica.object_persister.test_index.driverless'));
    }
}
