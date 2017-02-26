<?php

namespace FOS\ElasticaBundle\Tests\PropertyAccessor;

use FOS\ElasticaBundle\PropertyAccessor\CastToStringPropertyAccessor;

class IdentifierWithoutToString
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}

class IdentifierWithToString extends IdentifierWithoutToString
{
    public function __toString()
    {
        return (string) $this->id;
    }
}

class CastToStringPropertyAccessorTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCanCastObjectToString()
    {
        $id = new IdentifierWithToString('id');
        $array = array(
            'a' => $id
        );
        $propertyAccessor = new CastToStringPropertyAccessor();

        $this->assertEquals('id', $propertyAccessor->getValue($array, '[a]'));
    }

    public function testThatWontCastObjectWithoutToString()
    {
        $id = new IdentifierWithoutToString('id');
        $array = array(
            'a' => $id
        );
        $propertyAccessor = new CastToStringPropertyAccessor();

        $this->assertEquals($id, $propertyAccessor->getValue($array, '[a]'));
    }

    public function testThatWontCastIntToString()
    {
        $array = array(
            'a' => 1
        );
        $propertyAccessor = new CastToStringPropertyAccessor();

        $this->assertEquals(1, $propertyAccessor->getValue($array, '[a]'));
    }
}
