<?php
namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Event\AbstractEvent;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;

final class PostPersistEvent extends AbstractEvent implements PersistEvent
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
     * @var array
     */
    private $options;

    public function __construct(PagerInterface $pager, ObjectPersisterInterface $objectPersister, array $options)
    {
        $this->pager = $pager;
        $this->objectPersister = $objectPersister;
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
}
