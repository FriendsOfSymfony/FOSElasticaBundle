<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Transformer;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaIdentifierTransformer;
use PHPUnit\Framework\TestCase;
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

class ModelToElasticaIdentifierTransformerTest extends TestCase
{
    public function testGetDocumentWithIdentifierOnly()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), []);
        $data = $document->getData();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame(123, $document->getId());
        $this->assertCount(0, $data);
    }

    public function testGetDocumentWithIdentifierOnlyWithFields()
    {
        $transformer = $this->getTransformer();
        $document = $transformer->transform(new POPO(), ['name' => []]);
        $data = $document->getData();

        $this->assertInstanceOf(Document::class, $document);
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
