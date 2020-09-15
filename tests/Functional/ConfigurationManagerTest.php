<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

use FOS\ElasticaBundle\Configuration\IndexConfig;

/**
 * @group functional
 */
class ConfigurationManagerTest extends WebTestCase
{
    public function testContainerSource()
    {
        static::bootKernel(['test_case' => 'Basic']);
        $manager = $this->getManager();

        $index = $manager->getIndexConfiguration('index');

        $this->assertSame('index', $index->getName());
        $this->assertInstanceOf(IndexConfig::class, $index);
        //$this->assertInstanceOf(TypeConfig::class, $index->getType('parent'));
    }

    /**
     * @return \FOS\ElasticaBundle\Configuration\ConfigManager
     */
    private function getManager()
    {
        $manager = static::$kernel->getContainer()->get('fos_elastica.config_manager');

        return $manager;
    }
}
