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
use FOS\ElasticaBundle\Event\PreTransformEvent;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PreTransformEventTest extends TestCase
{
    public function testDocument()
    {
        $event = new PreTransformEvent($document = new Document(), [], new \stdClass());
        $this->assertSame($document, $event->getDocument());

        $event->setDocument($newDocument = new Document());
        $this->assertSame($newDocument, $event->getDocument());
    }

    public function testFields()
    {
        $event = new PreTransformEvent(new Document(), $fields = ['abc', '123'], new \stdClass());
        $this->assertSame($fields, $event->getFields());
    }

    public function testObject()
    {
        $event = new PreTransformEvent(new Document(), [], $object = new \stdClass());
        $this->assertSame($object, $event->getObject());
    }
}
