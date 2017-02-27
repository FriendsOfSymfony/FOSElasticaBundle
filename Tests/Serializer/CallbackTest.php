<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Serializer;

use FOS\ElasticaBundle\Serializer\Callback;

class CallbackTest extends \PHPUnit_Framework_TestCase
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
        $serializer = $this->getMockBuilder('Symfony\Component\Serializer\Serializer')->disableOriginalConstructor()->getMock();
        $callback->setSerializer($serializer);

        $callback->setGroups(array('foo'));
    }

    public function testSetGroupsFailsWithInvalidSerializer()
    {
        $callback = new Callback();
        $serializer = $this->getMockBuilder('FOS\ElasticaBundle\Tests\Serializer\FakeSerializer')->setMethods(array('serialize'))->getMock();
        $callback->setSerializer($serializer);

        $this->setExpectedException(
            'RuntimeException',
            'Setting serialization groups requires using "JMS\Serializer\Serializer" or '
                .'"Symfony\Component\Serializer\Serializer"'
        );

        $callback->setGroups(array('foo'));
    }
}

class FakeSerializer
{
    public function serialize()
    {
    }
}
