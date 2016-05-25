<?php

use \FOS\ElasticaBundle\Serializer\Callback;
use \Symfony\Component\Serializer\Serializer;

class CallbackTest extends PHPUnit_Framework_TestCase
{
    public function testSerializerMustHaveSerializeMethod()
    {
        $callback = new Callback();
        $this->setExpectedException(RuntimeException::class, 'The serializer must have a "serialize" method.');
        $callback->setSerializer(new \stdClass());
    }

    public function testSetGroupsWorksWithValidSerializer()
    {
        $callback = new Callback();
        $serializer = $this->prophesize(Serializer::class);
        $callback->setSerializer($serializer->reveal());

        $callback->setGroups(['foo']);
    }

    public function testSetGroupsFailsWithInvalidSerializer()
    {
        $callback = new Callback();
        $serializer = $this->getMockBuilder('FakeSerializer')->setMethods(['serialize'])->getMock();
        $callback->setSerializer($serializer);

        $this->setExpectedException(
            RuntimeException::class,
            'Setting serialization groups requires using "JMS\Serializer\Serializer" or '
                . '"Symfony\Component\Serializer\Serializer"'
        );

        $callback->setGroups(['foo']);
    }
}