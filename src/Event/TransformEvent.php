<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Elastica\Document;
use Symfony\Contracts\EventDispatcher\Event;

class TransformEvent extends Event
{
    /**
     * @var Document
     */
    private $document;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var object
     */
    private $object;

    public function __construct(Document $document, array $fields, object $object)
    {
        $this->document = $document;
        $this->fields = $fields;
        $this->object = $object;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function setDocument(Document $document)
    {
        $this->document = $document;
    }
}
