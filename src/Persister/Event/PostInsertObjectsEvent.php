<?php
namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Event\ElasticaEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;

final class PostInsertObjectsEvent extends ElasticaEvent implements PersistEvent
{
    /**
     * @var PagerInterface
     */
    private $pager;

    /**
     * @var ObjectPersisterInterface
     */
    private $objectPersister;

    /**
     * @var object[]
     */
    private $objects;

    /**
     * @var array
     */
    private $options;

    public function __construct(PagerInterface $pager, ObjectPersisterInterface $objectPersister, array $objects, array $options)
    {
        $this->pager = $pager;
        $this->objectPersister = $objectPersister;
        $this->objects = $objects;
        $this->options = $options;
    }

    /**
     * @return PagerInterface
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return ObjectPersisterInterface
     */
    public function getObjectPersister()
    {
        return $this->objectPersister;
    }

    /**
     * @return object[]
     */
    public function getObjects()
    {
        return $this->objects;
    }
}