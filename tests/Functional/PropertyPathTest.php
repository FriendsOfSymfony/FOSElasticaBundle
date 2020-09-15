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

use Elastica\Query\Match;

/**
 * @group functional
 */
class PropertyPathTest extends WebTestCase
{
    public function testContainerSource()
    {
        static::bootKernel(['test_case' => 'ORM']);
        /** @var \FOS\ElasticaBundle\Persister\ObjectPersister $persister */
        $persister = static::$kernel->getContainer()->get('fos_elastica.object_persister.property_paths_index');
        $obj = new TypeObj();
        $obj->coll = 'Hello';
        $persister->insertOne($obj);

        /** @var \Elastica\Index $index */
        $index = static::$kernel->getContainer()->get('fos_elastica.index.index');
        $index->refresh();

        $query = new Match();
        $query->setField('something', 'Hello');
        $search = $index->createSearch($query);

        $this->assertSame(1, $search->count());
    }
}
