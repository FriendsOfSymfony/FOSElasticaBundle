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
use FOS\ElasticaBundle\Event\PostTransformEvent;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PostTransformEventTest extends TestCase
{
    public function testDocument()
    {
        $event = new PostTransformEvent($document = new Document(), [], new \stdClass());
        $this->assertSame($document, $event->getDocument());
    }

    public function testFields()
    {
        $event = new PostTransformEvent(new Document(), $fields = ['abc', '123'], new \stdClass());
        $this->assertSame($fields, $event->getFields());
    }

    public function testObject()
    {
        $event = new PostTransformEvent(new Document(), [], $object = new \stdClass());
        $this->assertSame($object, $event->getObject());
    }
}
