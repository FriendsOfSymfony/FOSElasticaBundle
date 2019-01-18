<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Provider;

use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use Symfony\Component\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;

class PagerProviderRegistryTest extends TestCase
{
    protected function mockPagerProviderRegistry(array $providers, $service = null)
    {
        $container = new Container();
        $container->set('the_service_id', $service);

        $registry = new PagerProviderRegistry($providers);
        $registry->setContainer($container);
        return $registry;
    }

    public function testGetAllProviders()
    {
        $providers = [
            'index' => [
                'type' => 'the_service_id',
            ],
        ];
        $service = new \stdClass();
        $registry = $this->mockPagerProviderRegistry($providers, $service);
        $this->assertEquals(['index/type' => $service], $registry->getAllProviders('index', 'type'));
    }

    public function testGetIndexProvidersValid()
    {
        $providers = [
            'index' => [
                'type' => 'the_service_id',
            ],
        ];
        $service = new \stdClass();
        $registry = $this->mockPagerProviderRegistry($providers, $service);
        $this->assertEquals(['type' => $service], $registry->getIndexProviders('index', 'type'));
    }

    public function testGetIndexProvidersInvalid()
    {
        $registry = $this->mockPagerProviderRegistry([]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No providers were registered for index "index".');
        $registry->getIndexProviders('index');
    }

    public function testGetProviderValid()
    {
        $providers = [
            'index' => [
                'type' => 'the_service_id',
            ],
        ];
        $service = new \stdClass();
        $registry = $this->mockPagerProviderRegistry($providers, $service);
        $this->assertEquals($service, $registry->getProvider('index', 'type'));
    }

    public function testGetProviderInvalid()
    {
        $registry = $this->mockPagerProviderRegistry([]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No provider was registered for index "index" and type "type".');
        $registry->getProvider('index', 'type');
    }
}
