<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $document = $transformer->transform(new POPO(), array());
        $data = $document->getData();

        $this->assertInstanceOf('Elastica\Document', $document);
        $this->assertSame(123, $document->getId());
        $this->assertCount(0, $data);
    }

    public function testGetDocumentWithIdentifierOnlyWithFields()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), array('name' => array()));
        $data = $document->getData();

        $this->assertInstanceOf('Elastica\Document', $document);
        $this->assertSame(123, $document->getId());
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
