<?php

namespace FOS\ElasticaBundle\Tests\Transformer\ModelToElasticaIdentifierTransformer;

use FOS\ElasticaBundle\Transformer\ModelToElasticaIdentifierTransformer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class POPO
{
    protected $id = 123;
    protected $name = 'Name';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}

class ModelToElasticaIdentifierTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDocumentWithIdentifierOnly()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array());
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica\Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertCount(0, $data);
    }

    public function testGetDocumentWithIdentifierOnlyWithFields()
    {
        $transformer = $this->getTransformer();
        $document    = $transformer->transform(new POPO(), array('name' => array()));
        $data        = $document->getData();

        $this->assertInstanceOf('Elastica\Document', $document);
        $this->assertEquals(123, $document->getId());
        $this->assertCount(0, $data);
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
