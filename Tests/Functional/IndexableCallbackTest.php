<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

/**
 * @group functional
 */
class IndexableCallbackTest extends WebTestCase
{
    /**
     * 2 reasons for this test:.
     *
     * 1) To test that the configuration rename from indexable_callback under the listener
     * key is respected, and
     * 2) To test the Extension's set up of the Indexable service.
     */
    public function testIndexableCallback()
    {
        $client = $this->createClient(array('test_case' => 'ORM'));

        /** @var \FOS\ElasticaBundle\Provider\Indexable $in */
        $in = $client->getContainer()->get('fos_elastica.indexable');

        $this->assertTrue($in->isObjectIndexable('index', 'type', new TypeObj()));
        $this->assertTrue($in->isObjectIndexable('index', 'type2', new TypeObj()));
        $this->assertFalse($in->isObjectIndexable('index', 'type3', new TypeObj()));
        $this->assertFalse($in->isObjectIndexable('index', 'type4', new TypeObj()));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->deleteTmpDir('ORM');
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteTmpDir('ORM');
    }
}
