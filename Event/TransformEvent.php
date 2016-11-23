<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Elastica\Type;

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
     * @var mixed
     */
    private $document;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var mixed
     */
    private $object;

    /**
     * @var Type|null
     */
    private $type;

    /**
     * @param mixed     $document
     * @param array     $fields
     * @param mixed     $object
     * @param Type|null $type
     */
    public function __construct($document, array $fields, $object, Type $type = null)
    {
        $this->document = $document;
        $this->fields = $fields;
        $this->object = $object;
        $this->type = $type;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }
}
