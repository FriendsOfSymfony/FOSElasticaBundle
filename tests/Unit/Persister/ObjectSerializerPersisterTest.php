<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Persister;

use Elastica\Type;
use FOS\ElasticaBundle\Persister\ObjectSerializerPersister;
use FOS\ElasticaBundle\Serializer\Callback;
use FOS\ElasticaBundle\Transformer\ModelToElasticaIdentifierTransformer;
use FOS\ElasticaBundle\Tests\Unit\Mocks\ObjectSerializerPersisterPOPO as POPO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ObjectSerializerPersisterTest extends TestCase
{
    public function testThatCanReplaceObject()
    {
        $transformer = $this->getTransformer();

        $typeMock = $this->createMock(Type::class);
        $typeMock->expects($this->once())
            ->method('updateDocuments');

        $serializerMock = $this->createMock(Callback::class);
        $serializerMock->expects($this->once())->method('serialize');

        $objectPersister = new ObjectSerializerPersister($typeMock, $transformer, 'SomeClass', [$serializerMock, 'serialize']);
        $objectPersister->replaceOne(new POPO());
    }

    public function testThatCanInsertObject()
    {
        $transformer = $this->getTransformer();

        $typeMock = $this->createMock(Type::class);
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->once())
            ->method('addDocuments');

        $serializerMock = $this->createMock(Callback::class);
        $serializerMock->expects($this->once())->method('serialize');

        $objectPersister = new ObjectSerializerPersister($typeMock, $transformer, 'SomeClass', [$serializerMock, 'serialize']);
        $objectPersister->insertOne(new POPO());
    }

    public function testThatCanDeleteObject()
    {
        $transformer = $this->getTransformer();

        $typeMock = $this->createMock(Type::class);
        $typeMock->expects($this->once())
            ->method('deleteDocuments');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $serializerMock = $this->createMock(Callback::class);
        $serializerMock->expects($this->once())->method('serialize');

        $objectPersister = new ObjectSerializerPersister($typeMock, $transformer, 'SomeClass', [$serializerMock, 'serialize']);
        $objectPersister->deleteOne(new POPO());
    }

    public function testThatCanInsertManyObjects()
    {
        $transformer = $this->getTransformer();

        $typeMock = $this->createMock(Type::class);
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addObject');
        $typeMock->expects($this->never())
            ->method('addObjects');
        $typeMock->expects($this->once())
            ->method('addDocuments');

        $serializerMock = $this->createMock(Callback::class);
        $serializerMock->expects($this->exactly(2))->method('serialize');

        $objectPersister = new ObjectSerializerPersister($typeMock, $transformer, 'SomeClass', [$serializerMock, 'serialize']);
        $objectPersister->insertMany([new POPO(), new POPO()]);
    }

    /**
     * @return ModelToElasticaIdentifierTransformer
     */
    private function getTransformer()
    {
        $transformer = new ModelToElasticaIdentifierTransformer();
        $transformer->setPropertyAccessor(PropertyAccess::createPropertyAccessor());

        return $transformer;
    }
}
