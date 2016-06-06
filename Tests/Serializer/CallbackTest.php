<?php

use \FOS\ElasticaBundle\Serializer\Callback;

class CallbackTest extends PHPUnit_Framework_TestCase
{
    public function testSerializerMustHaveSerializeMethod()
    {
        $callback = new Callback();
        $this->setExpectedException('RuntimeException', 'The serializer must have a "serialize" method.');
        $callback->setSerializer(new \stdClass());
    }

    public function testSetGroupsWorksWithValidSerializer()
    {
        $callback = new Callback();
        $serializer = $this->getMock('Symfony\Component\Serializer\Serializer', [], [], '', false);
        $callback->setSerializer($serializer);

        $callback->setGroups(array('foo'));
    }

    public function testSetGroupsFailsWithInvalidSerializer()
    {
        $callback = new Callback();
        $serializer = $this->getMockBuilder('FakeSerializer')->setMethods(array('serialize'))->getMock();
        $callback->setSerializer($serializer);

        $this->setExpectedException(
            'RuntimeException',
            'Setting serialization groups requires using "JMS\Serializer\Serializer" or '
                . '"Symfony\Component\Serializer\Serializer"'
        );

        $callback->setGroups(array('foo'));
    }
}
