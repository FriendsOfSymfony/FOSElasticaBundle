<?php

namespace FOQ\ElasticaBundle\Tests\Provider;

use FOQ\ElasticaBundle\Provider\ProviderRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProviderRegistryTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $registry;

    public function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        // Mock ContainerInterface::get() to return the service ID
        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnArgument(0));

        $this->registry = new ProviderRegistry();
        $this->registry->setContainer($this->container);

        $this->registry->addProvider('foo', 'a', 'provider.foo.a');
        $this->registry->addProvider('foo', 'b', 'provider.foo.b');
        $this->registry->addProvider('foo', 'c', 'provider.foo.c');
        $this->registry->addProvider('bar', 'a', 'provider.bar.a');
        $this->registry->addProvider('bar', 'b', 'provider.bar.b');
    }

    public function testGetAllProviders()
    {
        $this->assertEquals(array(
            'provider.foo.a',
            'provider.foo.b',
            'provider.foo.c',
            'provider.bar.a',
            'provider.bar.b',
        ), $this->registry->getAllProviders());
    }

    public function testGetIndexProviders()
    {
        $this->assertEquals(array(
            'provider.foo.a',
            'provider.foo.b',
            'provider.foo.c',
        ), $this->registry->getIndexProviders('foo'));

        $this->assertEquals(array(
            'provider.bar.a',
            'provider.bar.b',
        ), $this->registry->getIndexProviders('bar'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetIndexProvidersWithInvalidIndex()
    {
        $this->registry->getIndexProviders('baz');
    }

    public function testGetProvider()
    {
        $this->assertEquals('provider.foo.a', $this->registry->getProvider('foo', 'a'));
        $this->assertEquals('provider.foo.b', $this->registry->getProvider('foo', 'b'));
        $this->assertEquals('provider.foo.c', $this->registry->getProvider('foo', 'c'));
        $this->assertEquals('provider.bar.a', $this->registry->getProvider('bar', 'a'));
        $this->assertEquals('provider.bar.b', $this->registry->getProvider('bar', 'b'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetProviderWithInvalidIndexAndType()
    {
        $this->registry->getProvider('bar', 'c');
    }
}
