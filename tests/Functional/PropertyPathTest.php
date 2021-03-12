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

use Elastica\Index;
use Elastica\Query\MatchQuery;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

/**
 * @group functional
 */
class PropertyPathTest extends WebTestCase
{
    public function testContainerSource()
    {
        self::bootKernel(['test_case' => 'ORM']);
        /** @var ObjectPersisterInterface $persister */
        $persister = self::$container->get('fos_elastica.object_persister.property_paths_index');
        $obj = new TypeObj();
        $obj->coll = 'Hello';
        $persister->insertOne($obj);

        /** @var Index $index */
        $index = self::$container->get('fos_elastica.index.index');
        $index->refresh();

        $query = new MatchQuery();
        $query->setField('something', 'Hello');
        $search = $index->createSearch($query);

        $this->assertSame(1, $search->count());
    }
}
