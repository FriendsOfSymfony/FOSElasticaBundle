<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Elastica\Document;
use Symfony\Component\EventDispatcher\Event;

class TransformEvent extends Event
{
    /**
     * @Event("FOS\ElasticaBundle\Event\TransformEvent")
     */
    const PRE_TRANSFORM = 'fos_elastica.pre_transform';

    /**
     * @Event("FOS\ElasticaBundle\Event\TransformEvent")
     */
    const POST_TRANSFORM = 'fos_elastica.post_transform';

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

    /**
     * @param mixed $document
     * @param array $fields
     * @param object $object
     */
    public function __construct($document, array $fields, $object)
    {
        $this->document = $document;
        $this->fields = $fields;
        $this->object = $object;
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Document $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }
}
