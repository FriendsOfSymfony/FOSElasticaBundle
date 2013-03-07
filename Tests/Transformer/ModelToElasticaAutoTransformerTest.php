<?php

namespace FOQ\ElasticaBundle\Tests\Transformer\ModelToElasticaAutoTransformer;

use FOQ\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;

class POPO
{
    public $id = 123;
    public $name = 'someName';
    private $desc = 'desc';
    public $float = 7.2;
    public $bool = true;
    public $falseBool = false;
    public $date;
    public $nullValue;
    public $file;
    public $fileContents;

    public function __construct()
    {
        $this->date         = new \DateTime('1979-05-05');
        $this->file         = new \SplFileInfo(__DIR__ . '/../fixtures/attachment.odt');
        $this->fileContents = file_get_contents(__DIR__ . '/../fixtures/attachment.odt');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIterator()
    {
        $iterator = new \ArrayIterator();
        $iterator->append('value1');

        return $iterator;
    }

    public function getArray()
    {
        return array(
            'key1' => 'value1',
            'key2' => 'value2'
        );
    }

    public function getMultiArray()
    {
        return array(
            'key1'  => 'value1',
            'key2'  => array('value2', false, 123, 8.9, new \DateTime('1978-09-07')),
        );
    }

    public function getBool()
    {
        return $this->bool;
    }

    public function getFalseBool()
    {
        return $this->falseBool;
    }

    public function getFloat()
    {
        return $this->float;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getNullValue()
    {
        return $this->nullValue;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFileContents()
    {
        return $this->file;
    }

    public function getSub()
    {
        return array(
            (object) array('foo' => 'foo', 'bar' => 'foo', 'id' => 1),
            (object) array('foo' => 'bar', 'bar' => 'bar', 'id' => 2),
        );
    }

    public function getUpper()
    {
        return (object) array('id' => 'parent', 'name' => 'a random name');
    }
}

class ModelToElasticaAutoTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('Elastica_Document')) {
            ;
            $this->markTestSkipped('The Elastica library classes are not available');
        }
    }

    public function testThatCanTransformObject()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('name' => array()));
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica_Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertEquals('someName', $data['name']);
    }

    public function testThatCanTransformObjectWithCorrectTypes()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(
            new POPO(), array(
                             'name'      => array(),
                             'float'     => array(),
                             'bool'      => array(),
                             'date'      => array(),
                             'falseBool' => array()
                        )
        );
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica_Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertEquals('someName', $data['name']);
        $this->assertEquals(7.2, $data['float']);
        $this->assertEquals(true, $data['bool']);
        $this->assertEquals(false, $data['falseBool']);
        $expectedDate = new \DateTime('1979-05-05');
        $this->assertEquals($expectedDate->format('c'), $data['date']);
    }

    public function testThatCanTransformObjectWithIteratorValue()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('iterator' => array()));
        $data        = $document->getData();

        $this->assertEquals(array('value1'), $data['iterator']);
    }

    public function testThatCanTransformObjectWithArrayValue()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('array' => array()));
        $data        = $document->getData();

        $this->assertEquals(
            array(
                 'key1'  => 'value1',
                 'key2'  => 'value2'
            ), $data['array']
        );
    }

    public function testThatCanTransformObjectWithMultiDimensionalArrayValue()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('multiArray' => array()));
        $data        = $document->getData();

        $expectedDate = new \DateTime('1978-09-07');

        $this->assertEquals(
            array(
                 'key1'  => 'value1',
                 'key2'  => array('value2', false, 123, 8.9, $expectedDate->format('c')),
            ), $data['multiArray']
        );
    }

    public function testThatNullValuesAreNotFilteredOut()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('nullValue' => array()));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('nullValue', $data));
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\PropertyAccessDeniedException
     */
    public function testThatCannotTransformObjectWhenGetterDoesNotExistForPrivateMethod()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $transformer->transform(new POPO(), array('desc' => array()));
    }

    public function testFileAddedForAttachmentMapping()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('file' => array('type' => 'attachment')));
        $data        = $document->getData();

        $this->assertEquals(base64_encode(file_get_contents(__DIR__ . '/../fixtures/attachment.odt')), $data['file']);
    }

    public function testFileContentsAddedForAttachmentMapping()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array('fileContents' => array('type' => 'attachment')));
        $data        = $document->getData();

        $this->assertEquals(
            base64_encode(file_get_contents(__DIR__ . '/../fixtures/attachment.odt')), $data['fileContents']
        );
    }

    public function testNestedMapping()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array(
                'sub' => array(
                    'type' => 'nested',
                    'properties' => array('foo' => '~')
                    )
                ));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('sub', $data));
        $this->assertInternalType('array', $data['sub']);
        $this->assertEquals(array(
             array('foo' => 'foo'),
             array('foo' => 'bar')
           ), $data['sub']);
    }

    public function tesObjectMapping()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array(
                'sub' => array(
                    'type' => 'object',
                    'properties' => array('bar')
                    )
                ));
        $data        = $document->getData();

        $this->assertTrue(array_key_exists('sub', $data));
        $this->assertInternalType('array', $data['sub']);
        $this->assertEquals(array(
             array('bar' => 'foo'),
             array('bar' => 'bar')
           ), $data['sub']);
    }

    public function testParentMapping()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array(
                'upper' => array(
                    '_parent' => array('type' => 'upper', 'identifier' => 'id'),
                    )
                ));

        $this->assertEquals("parent", $document->getParent());
    }

    public function testParentMappingWithCustomIdentifier()
    {
        $transformer = new ModelToElasticaAutoTransformer();
        $document    = $transformer->transform(new POPO(), array(
                'upper' => array(
                    '_parent' => array('type' => 'upper', 'identifier' => 'name'),
                    )
                ));

        $this->assertEquals("a random name", $document->getParent());
    }
}
