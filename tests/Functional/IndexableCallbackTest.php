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

use FOS\ElasticaBundle\Provider\Indexable;

/**
 * @group functional
 *
 * @internal
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
        self::bootKernel(['test_case' => 'ORM']);

        /** @var Indexable $in */
        $in = $this->getContainerBC()->get('test_alias.fos_elastica.indexable');

        $this->assertTrue($in->isObjectIndexable('index', new TypeObj()));
        $this->assertTrue($in->isObjectIndexable('third_index', new TypeObj()));
        $this->assertFalse($in->isObjectIndexable('fourth_index', new TypeObj()));
        $this->assertFalse($in->isObjectIndexable('fifth_index', new TypeObj()));
    }
}
