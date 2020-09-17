<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Event;

use Elastica\Document;
use FOS\ElasticaBundle\Event\TransformEvent;
use PHPUnit\Framework\TestCase;

class TransformEventTest extends TestCase
{
    /**
     * @var TransformEvent
     */
    private $event;

    protected function setUp(): void
    {
        $document = new Document();
        $object = (object) [];
        $this->event = new TransformEvent($document, [], $object);
    }

    public function testDocument()
    {
        $this->assertNotNull($this->event->getDocument());
        $document = new Document();
        $this->event->setDocument($document);
        $this->assertEquals($document, $this->event->getDocument());
    }

    public function testFields()
    {
        $document = new Document();
        $object = (object) [];
        $fields = ['abc', '123'];
        $event = new TransformEvent($document, $fields, $object);
        $this->assertEquals($fields, $event->getFields());
    }

    public function testObject()
    {
        $document = new Document();
        $object = (object) ['abc', '123'];
        $event = new TransformEvent($document, [], $object);
        $this->assertEquals($object, $event->getObject());
    }
}
