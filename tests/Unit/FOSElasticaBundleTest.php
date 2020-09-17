<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit;

use FOS\ElasticaBundle\FOSElasticaBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FOSElasticaBundleTest extends TestCase
{
    public function testCompilerPassesAreRegistered()
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container
            ->expects($this->atLeastOnce())
            ->method('addCompilerPass');

        $bundle = new FOSElasticaBundle();
        $bundle->build($container);
    }
}
