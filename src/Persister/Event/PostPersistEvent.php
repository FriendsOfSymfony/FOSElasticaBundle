<?php

namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class PostPersistEvent extends Event implements PersistEvent
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
    public function getPager(): ?PagerInterface
    {
        return $this->pager;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return ObjectPersisterInterface
     */
    public function getObjectPersister(): ObjectPersisterInterface
    {
        return $this->objectPersister;
    }
}
