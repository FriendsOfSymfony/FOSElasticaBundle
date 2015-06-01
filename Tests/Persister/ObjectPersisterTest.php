<?php

namespace FOS\ElasticaBundle\Tests\ObjectPersister;

use FOS\ElasticaBundle\Event\PersistingEvent;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class POPO
{
    public $id   = 123;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return 'popoName';
    }
}

class InvalidObjectPersister extends ObjectPersister
{
    public function transformToElasticaDocument($object)
    {
        throw new \BadMethodCallException('Invalid transformation');
    }
}

class ObjectPersisterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function setUp()
    {
        $this->dispatcher = $this->getMockBuilder('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface')
            ->getMock();
    }

    public function testThatCanReplaceObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('updateDocuments');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->replaceOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotReplaceObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->replaceOne(new POPO());
    }

    public function testThatCanInsertObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->once())
            ->method('addDocuments');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->insertOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotInsertObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->insertOne(new POPO());
    }

    public function testThatCanDeleteObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->once())
            ->method('deleteDocuments');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->deleteOne(new POPO());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotDeleteObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->deleteOne(new POPO());
    }

    public function testThatCanInsertManyObjects()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        $typeMock->expects($this->once())
            ->method('addDocuments');

        $this->dispatcherExpects(array(
            array(PersistingEvent::INSERT_OBJECTS, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\PersistingEvent')),
        ));

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->setEventDispatcher($this->dispatcher);
        $objectPersister->insertMany(array(new POPO(), new POPO()));
    }

    public function testThatCanReplaceManyObjects()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        $typeMock->expects($this->never())
            ->method('addDocuments');
        $typeMock->expects($this->once())
            ->method('updateDocuments');

        $this->dispatcherExpects(array(
            array(PersistingEvent::REPLACE_OBJECTS, $this->isInstanceOf('FOS\\ElasticaBundle\\Event\\PersistingEvent')),
        ));

        $fields = array('name' => array());

        $objectPersister = new ObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->setEventDispatcher($this->dispatcher);
        $objectPersister->replaceMany(array(new POPO(), new POPO()));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testThatErrorIsHandledWhenCannotInsertManyObject()
    {
        $transformer = $this->getTransformer();

        /** @var $typeMock \PHPUnit_Framework_MockObject_MockObject|\Elastica\Type */
        $typeMock = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeMock->expects($this->never())
            ->method('deleteById');
        $typeMock->expects($this->never())
            ->method('addDocument');
        $typeMock->expects($this->never())
            ->method('addDocuments');

        $fields = array('name' => array());

        $objectPersister = new InvalidObjectPersister($typeMock, $transformer, 'SomeClass', $fields);
        $objectPersister->insertMany(array(new POPO(), new POPO()));
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

    private function dispatcherExpects(array $events)
    {
        $expectation = $this->dispatcher->expects($this->exactly(count($events)))
            ->method('dispatch');

        call_user_func_array(array($expectation, 'withConsecutive'), $events);
    }
}
