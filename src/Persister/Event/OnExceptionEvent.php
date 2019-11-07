<?php

namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class OnExceptionEvent extends Event implements PersistEvent
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
     * @var \Exception
     */
    private $exception;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $objects;

    /**
     * @var bool
     */
    private $ignore;

    public function __construct(
        PagerInterface $pager, 
        ObjectPersisterInterface $objectPersister, 
        \Exception $exception, 
        array $objects, 
        array $options
    ) {
        $this->pager = $pager;
        $this->objectPersister = $objectPersister;
        $this->exception = $exception;
        $this->options = $options;

        $this->ignore = false;
        $this->objects = $objects;
    }

    public function getPager(): PagerInterface
    {
        return $this->pager;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getObjectPersister(): ObjectPersisterInterface
    {
        return $this->objectPersister;
    }

    public function getException(): \Exception
    {
        return $this->exception;
    }

    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    public function isIgnored(): bool
    {
        return $this->ignore;
    }

    public function setIgnore(bool $ignore)
    {
        $this->ignore = $ignore;
    }

    public function getObjects(): array
    {
        return $this->objects;
    }
}
