<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Provider;

use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 */
class PagerProviderRegistryTest extends TestCase
{
    public function testGetProviders()
    {
        $service = $this->createMock(PagerProviderInterface::class);

        $providers = new ServiceLocator([
            'index' => static function () use ($service) {
                return $service;
            },
        ]);

        $registry = new PagerProviderRegistry($providers);
        $this->assertEquals(['index' => $service], $registry->getProviders());
    }

    public function testGetProviderValid()
    {
        $service = $this->createMock(PagerProviderInterface::class);

        $providers = new ServiceLocator([
            'index' => static function () use ($service) {
                return $service;
            },
        ]);

        $registry = new PagerProviderRegistry($providers);
        $this->assertEquals($service, $registry->getProvider('index'));
    }

    public function testGetProviderInvalid()
    {
        $registry = new PagerProviderRegistry(new ServiceLocator([]));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No provider was registered for index "index".');
        $registry->getProvider('index');
    }
}
