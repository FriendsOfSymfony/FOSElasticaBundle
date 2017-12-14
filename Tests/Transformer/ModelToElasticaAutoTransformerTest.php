<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Transformer\ModelToElasticaAutoTransformer;

use Elastica\Document;
use FOS\ElasticaBundle\Event\TransformEvent;
use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class POPO
{
    public $id = 123;
    public $name = 'someName';
    public $float = 7.2;
    public $bool = true;
    public $falseBool = false;
    public $date;
    public $nullValue;
    public $file;
    public $fileContents;
    private $desc = 'desc';

    public function __construct()
    {
        $this->date = new \DateTime('1979-05-05');
        $this->file = new \SplFileInfo(__DIR__.'/../fixtures/attachment.odt');
        $this->fileContents = file_get_contents(__DIR__.'/../fixtures/attachment.odt');
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
        return [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
    }

    public function getMultiArray()
    {
        return [
            'key1' => 'value1',
            'key2' => ['value2', false, 123, 8.9, new \DateTime('1978-09-07')],
        ];
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
        return $this->fileContents;
    }

    public function getSub()
    {
        return [
            (object) ['foo' => 'foo', 'bar' => 'foo', 'id' => 1],
            (object) ['foo' => 'bar', 'bar' => 'bar', 'id' => 2],
        ];
    }

    public function getObj()
    {
        return ['foo' => 'foo', 'bar' => 'foo', 'id' => 1];
    }

    public function getNestedObject()
    {
        return ['key1' => (object) ['id' => 1, 'key1sub1' => 'value1sub1', 'key1sub2' => 'value1sub2']];
    }

    public function getUpper()
    {
        return (object) ['id' => 'parent', 'name' => 'a random name'];
    }

    public function getUpperAlias()
    {
        return $this->getUpper();
    }

    public function getObjWithoutIdentifier()
    {
        return (object) ['foo' => 'foo', 'bar' => 'foo'];
    }

    public function getSubWithoutIdentifier()
    {
        return [
            (object) ['foo' => 'foo', 'bar' => 'foo'],
            (object) ['foo' => 'bar', 'bar' => 'bar'],
        ];
    }
}

class CastableObject
{
    public $foo;

    public function __toString()
    {
        return $this->foo;
    }
}

class ModelToElasticaAutoTransformerTest extends TestCase
{
    public function testTransformerDispatches()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    TransformEvent::PRE_TRANSFORM,
                    $this->isInstanceOf(TransformEvent::class),
                ],
                [
                    TransformEvent::POST_TRANSFORM,
                    $this->isInstanceOf(TransformEvent::class),
                ]
            );

        $transformer = $this->getTransformer($dispatcher);
        $transformer->transform(new POPO(), []);
    }

    public function testPropertyPath()
    {
        $transformer = $this->getTransformer();

        $document = $transformer->transform(new POPO(), ['name' => ['property_path' => false]]);
        $this->assertInstanceOf(Document::class, $document);
        $this->assertFalse($document->has('name'));

        $document = $transformer->transform(new POPO(), ['realName' => ['property_path' => 'name']]);
        $this->assertInstanceOf(Document::class, $document);
        $this->assertTrue($document->has('realName'));
        $this->assertSame('someName', $document->get('realName'));
    }

    public function testThatCanTransformObject()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), ['name' => []]);
        $data = $document->getData();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame(123, $document->getId());
        $this->assertSame('someName', $data['name']);
    }

    public function testThatCanTransformObjectWithCorrectTypes()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(
            new POPO(), [
                             'name' => [],
                             'float' => [],
                             'bool' => [],
                             'date' => [],
                             'falseBool' => [],
                        ]
        );
        $data = $document->getData();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame(123, $document->getId());
        $this->assertSame('someName', $data['name']);
        $this->assertSame(7.2, $data['float']);
        $this->assertTrue($data['bool']);
        $this->assertFalse($data['falseBool']);
        $expectedDate = new \DateTime('1979-05-05');
        $this->assertSame($expectedDate->format('c'), $data['date']);
    }

    public function testThatCanTransformObjectWithIteratorValue()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), ['iterator' => []]);
        $data = $document->getData();

        $this->assertSame(['value1'], $data['iterator']);
    }

    public function testThatCanTransformObjectWithArrayValue()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), ['array' => []]);
        $data = $document->getData();

        $this->assertSame(
            [
                 'key1' => 'value1',
                 'key2' => 'value2',
            ], $data['array']
        );
    }

    public function testThatCanTransformObjectWithMultiDimensionalArrayValue()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), ['multiArray' => []]);
        $data = $document->getData();

        $expectedDate = new \DateTime('1978-09-07');

        $this->assertSame(
            [
                 'key1' => 'value1',
                 'key2' => ['value2', false, 123, 8.9, $expectedDate->format('c')],
            ], $data['multiArray']
        );
    }

    public function testThatNullValuesAreNotFilteredOut()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), ['nullValue' => []]);
        $data = $document->getData();

        $this->assertTrue(array_key_exists('nullValue', $data));
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\RuntimeException
     */
    public function testThatCannotTransformObjectWhenGetterDoesNotExistForPrivateMethod()
    {
        $transformer = $this->getTransformer();
        $transformer->transform(new POPO(), ['desc' => []]);
    }

    public function testFileAddedForAttachmentMapping()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), ['file' => ['type' => 'attachment']]);
        $data = $document->getData();

        $this->assertSame(base64_encode(file_get_contents(__DIR__.'/../fixtures/attachment.odt')), $data['file']);
    }

    public function testFileContentsAddedForAttachmentMapping()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), ['fileContents' => ['type' => 'attachment']]);
        $data = $document->getData();

        $this->assertSame(
            base64_encode(file_get_contents(__DIR__.'/../fixtures/attachment.odt')), $data['fileContents']
        );
    }

    public function testNestedMapping()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            'sub' => [
                'type' => 'nested',
                'properties' => ['foo' => []],
            ],
        ]);
        $data = $document->getData();

        $this->assertTrue(array_key_exists('sub', $data));
        $this->assertInternalType('array', $data['sub']);
        $this->assertSame([
             ['foo' => 'foo'],
             ['foo' => 'bar'],
           ], $data['sub']);
    }

    public function tesObjectMapping()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
                'sub' => [
                    'type' => 'object',
                    'properties' => ['bar'],
                    ],
                ]);
        $data = $document->getData();

        $this->assertTrue(array_key_exists('sub', $data));
        $this->assertInternalType('array', $data['sub']);
        $this->assertSame([
             ['bar' => 'foo'],
             ['bar' => 'bar'],
           ], $data['sub']);
    }

    public function testObjectDoesNotRequireProperties()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
                'obj' => [
                    'type' => 'object',
                    ],
                ]);
        $data = $document->getData();

        $this->assertTrue(array_key_exists('obj', $data));
        $this->assertInternalType('array', $data['obj']);
        $this->assertSame([
             'foo' => 'foo',
             'bar' => 'foo',
             'id' => 1,
       ], $data['obj']);
    }

    public function testObjectsMappingOfAtLeastOneAutoMappedObjectAndAtLeastOneManuallyMappedObject()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(
            new POPO(),
            [
                'obj' => ['type' => 'object', 'properties' => []],
                'nestedObject' => [
                    'type' => 'object',
                    'properties' => [
                        'key1sub1' => [
                            'type' => 'text',
                            'properties' => [],
                        ],
                        'key1sub2' => [
                            'type' => 'text',
                            'properties' => [],
                        ],
                    ],
                ],
            ]
        );
        $data = $document->getData();

        $this->assertTrue(array_key_exists('obj', $data));
        $this->assertTrue(array_key_exists('nestedObject', $data));
        $this->assertInternalType('array', $data['obj']);
        $this->assertInternalType('array', $data['nestedObject']);
        $this->assertSame(
            [
                'foo' => 'foo',
                'bar' => 'foo',
                'id' => 1,
            ],
            $data['obj']
        );
        $this->assertSame(
            [
                'key1sub1' => 'value1sub1',
                'key1sub2' => 'value1sub2',
            ],
            $data['nestedObject'][0]
        );
    }

    public function testParentMapping()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            '_parent' => ['type' => 'upper', 'property' => 'upper', 'identifier' => 'id'],
        ]);

        $this->assertSame('parent', $document->getParent());
    }

    public function testParentMappingWithCustomIdentifier()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            '_parent' => ['type' => 'upper', 'property' => 'upper', 'identifier' => 'name'],
        ]);

        $this->assertSame('a random name', $document->getParent());
    }

    public function testParentMappingWithNullProperty()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            '_parent' => ['type' => 'upper', 'property' => null, 'identifier' => 'id'],
        ]);

        $this->assertSame('parent', $document->getParent());
    }

    public function testParentMappingWithCustomProperty()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            '_parent' => ['type' => 'upper', 'property' => 'upperAlias', 'identifier' => 'id'],
        ]);

        $this->assertSame('parent', $document->getParent());
    }

    public function testThatMappedObjectsDontNeedAnIdentifierField()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            'objWithoutIdentifier' => [
                'type' => 'object',
                'properties' => [
                    'foo' => [],
                    'bar' => [],
                ],
            ],
        ]);
        $data = $document->getData();

        $this->assertTrue(array_key_exists('objWithoutIdentifier', $data));
        $this->assertInternalType('array', $data['objWithoutIdentifier']);
        $this->assertSame([
            'foo' => 'foo',
            'bar' => 'foo',
        ], $data['objWithoutIdentifier']);
    }

    public function testThatNestedObjectsDontNeedAnIdentifierField()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            'subWithoutIdentifier' => [
                'type' => 'nested',
                'properties' => [
                    'foo' => [],
                    'bar' => [],
                ],
            ],
        ]);
        $data = $document->getData();

        $this->assertTrue(array_key_exists('subWithoutIdentifier', $data));
        $this->assertInternalType('array', $data['subWithoutIdentifier']);
        $this->assertSame([
            ['foo' => 'foo', 'bar' => 'foo'],
            ['foo' => 'bar', 'bar' => 'bar'],
        ], $data['subWithoutIdentifier']);
    }

    public function testNestedTransformHandlesSingleObjects()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            'upper' => [
                'type' => 'nested',
                'properties' => ['name' => null],
            ],
        ]);

        $data = $document->getData();
        $this->assertSame('a random name', $data['upper']['name']);
    }

    public function testNestedTransformReturnsAnEmptyArrayForNullValues()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), [
            'nullValue' => [
                'type' => 'nested',
                'properties' => [
                    'foo' => [],
                    'bar' => [],
                ],
            ],
        ]);

        $data = $document->getData();
        $this->assertInternalType('array', $data['nullValue']);
        $this->assertEmpty($data['nullValue']);
    }

    public function testUnmappedFieldValuesAreNormalisedToStrings()
    {
        $object = new \stdClass();
        $value = new CastableObject();
        $value->foo = 'bar';

        $object->id = 123;
        $object->unmappedValue = $value;

        $transformer = $this->getTransformer();
        $document = $transformer->transform($object, ['unmappedValue' => ['property' => 'unmappedValue']]);

        $data = $document->getData();
        $this->assertSame('bar', $data['unmappedValue']);
    }

    /**
     * @param null|\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     *
     * @return ModelToElasticaAutoTransformer
     */
    private function getTransformer($dispatcher = null)
    {
        $transformer = new ModelToElasticaAutoTransformer([], $dispatcher);
        $transformer->setPropertyAccessor(PropertyAccess::createPropertyAccessor());

        return $transformer;
    }
}
