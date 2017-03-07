<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Provider;

use FOS\ElasticaBundle\Provider\ProviderRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProviderRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $container;
    private $registry;

    public function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();

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
        $allProviders = [
            'foo/a' => 'provider.foo.a',
            'foo/b' => 'provider.foo.b',
            'foo/c' => 'provider.foo.c',
            'bar/a' => 'provider.bar.a',
            'bar/b' => 'provider.bar.b',
        ];

        $this->assertSame($allProviders, $this->registry->getAllProviders());
    }

    public function testGetIndexProviders()
    {
        $fooProviders = [
            'a' => 'provider.foo.a',
            'b' => 'provider.foo.b',
            'c' => 'provider.foo.c',
        ];

        $barProviders = [
            'a' => 'provider.bar.a',
            'b' => 'provider.bar.b',
        ];

        $this->assertSame($fooProviders, $this->registry->getIndexProviders('foo'));
        $this->assertSame($barProviders, $this->registry->getIndexProviders('bar'));
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
        $this->assertSame('provider.foo.a', $this->registry->getProvider('foo', 'a'));
        $this->assertSame('provider.foo.b', $this->registry->getProvider('foo', 'b'));
        $this->assertSame('provider.foo.c', $this->registry->getProvider('foo', 'c'));
        $this->assertSame('provider.bar.a', $this->registry->getProvider('bar', 'a'));
        $this->assertSame('provider.bar.b', $this->registry->getProvider('bar', 'b'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetProviderWithInvalidIndexAndType()
    {
        $this->registry->getProvider('bar', 'c');
    }
}
