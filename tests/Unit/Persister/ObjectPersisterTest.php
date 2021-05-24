<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Persister;

use Elastica\Document;
use Elastica\Index;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Tests\Unit\Mocks\ObjectPersisterPOPO as POPO;
use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class InvalidObjectPersister extends ObjectPersister
{
    public function transformToElasticaDocument(object $object): Document
    {
        throw new \BadMethodCallException('Invalid transformation');
    }
}

/**
 * @internal
 */
class ObjectPersisterTest extends TestCase
{
    public function testThatCanReplaceObject()
    {
        $transformer = $this->getTransformer();

        $indexMock = $this->createMock(Index::class);
        $indexMock->expects($this->once())
            ->method('updateDocuments')
        ;

        $fields = ['name' => []];

        $objectPersister = new ObjectPersister($indexMock, $transformer, 'SomeClass', $fields);
        $objectPersister->replaceOne(new POPO());
    }

    public function testThatErrorIsHandledWhenCannotReplaceObject()
    {
        $transformer = $this->getTransformer();

        $indexMock = $this->createMock(Index::class);
        $indexMock->expects($this->never())
            ->method('deleteById')
        ;
        $indexMock->expects($this->never())
            ->method('addDocument')
        ;

        $fields = ['name' => []];

        $objectPersister = new InvalidObjectPersister($indexMock, $transformer, 'SomeClass', $fields);

        $this->expectException(\BadMethodCallException::class);
        $objectPersister->replaceOne(new POPO());
    }

    public function testThatCanInsertObject()
    {
        $transformer = $this->getTransformer();

        $indexMock = $this->createMock(Index::class);
        $indexMock->expects($this->never())
            ->method('deleteById')
        ;
        $indexMock->expects($this->once())
            ->method('addDocuments')
        ;

        $fields = ['name' => []];

        $objectPersister = new ObjectPersister($indexMock, $transformer, 'SomeClass', $fields);
        $objectPersister->insertOne(new POPO());
    }

    public function testThatErrorIsHandledWhenCannotInsertObject()
    {
        $transformer = $this->getTransformer();

        $indexMock = $this->createMock(Index::class);
        $indexMock->expects($this->never())
            ->method('deleteById')
        ;
        $indexMock->expects($this->never())
            ->method('addDocument')
        ;

        $fields = ['name' => []];

        $objectPersister = new InvalidObjectPersister($indexMock, $transformer, 'SomeClass', $fields);

        $this->expectException(\BadMethodCallException::class);
        $objectPersister->insertOne(new POPO());
    }

    public function testThatCanDeleteObject()
    {
        $transformer = $this->getTransformer();

        $indexMock = $this->createMock(Index::class);
        $indexMock->expects($this->once())
            ->method('deleteDocuments')
        ;
        $indexMock->expects($this->never())
            ->method('addDocument')
        ;

        $fields = ['name' => []];

        $objectPersister = new ObjectPersister($indexMock, $transformer, 'SomeClass', $fields);
        $objectPersister->deleteOne(new POPO());
    }

    public function testThatErrorIsHandledWhenCannotDeleteObject()
    {
        $transformer = $this->getTransformer();

        $indexMock = $this->createMock(Index::class);
        $indexMock->expects($this->never())
            ->method('deleteById')
        ;
        $indexMock->expects($this->never())
            ->method('addDocument')
        ;

        $fields = ['name' => []];

        $objectPersister = new InvalidObjectPersister($indexMock, $transformer, 'SomeClass', $fields);

        $this->expectException(\BadMethodCallException::class);
        $objectPersister->deleteOne(new POPO());
    }

    public function testThatCanInsertManyObjects()
    {
        $transformer = $this->getTransformer();

        $indexMock = $this->createMock(Index::class);
        $indexMock->expects($this->never())
            ->method('deleteById')
        ;
        $indexMock->expects($this->never())
            ->method('addDocument')
        ;
        $indexMock->expects($this->once())
            ->method('addDocuments')
        ;

        $fields = ['name' => []];

        $objectPersister = new ObjectPersister($indexMock, $transformer, 'SomeClass', $fields);
        $objectPersister->insertMany([new POPO(), new POPO()]);
    }

    public function testThatErrorIsHandledWhenCannotInsertManyObject()
    {
        $transformer = $this->getTransformer();

        $indexMock = $this->createMock(Index::class);
        $indexMock->expects($this->never())
            ->method('deleteById')
        ;
        $indexMock->expects($this->never())
            ->method('addDocument')
        ;
        $indexMock->expects($this->never())
            ->method('addDocuments')
        ;

        $fields = ['name' => []];

        $objectPersister = new InvalidObjectPersister($indexMock, $transformer, 'SomeClass', $fields);

        $this->expectException(\BadMethodCallException::class);
        $objectPersister->insertMany([new POPO(), new POPO()]);
    }

    /**
     * @return ModelToElasticaAutoTransformer
     */
    private function getTransformer()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $transformer->setPropertyAccessor(PropertyAccess::createPropertyAccessor());

        return $transformer;
    }
}
