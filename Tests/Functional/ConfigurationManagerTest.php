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
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @group functional
 */
class ConfigurationManagerTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->deleteTmpDir('Basic');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteTmpDir('Basic');
    }

    public function testContainerSource()
    {
        static::bootKernel(['test_case' => 'Basic']);
        $manager = $this->getManager();

        $index = $manager->getIndexConfiguration('index');

        $this->assertSame('index', $index->getName());
        $this->assertGreaterThanOrEqual(2, count($index->getTypes()));
        $this->assertInstanceOf('FOS\\ElasticaBundle\\Configuration\\TypeConfig', $index->getType('type'));
        $this->assertInstanceOf('FOS\\ElasticaBundle\\Configuration\\TypeConfig', $index->getType('parent'));
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
