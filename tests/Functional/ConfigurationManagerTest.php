<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Configuration\IndexConfig;

/**
 * @group functional
 */
class ConfigurationManagerTest extends WebTestCase
{
    public function testContainerSource()
    {
        static::bootKernel(['test_case' => 'Basic']);
        /** @var ConfigManager $manager */
        $manager = self::$container->get('fos_elastica.config_manager');

        $index = $manager->getIndexConfiguration('index');

        $this->assertSame('index', $index->getName());
        $this->assertInstanceOf(IndexConfig::class, $index);
    }
}
