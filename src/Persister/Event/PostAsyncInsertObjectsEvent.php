<?php

namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class PostAsyncInsertObjectsEvent extends Event implements PersistEvent
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
     * @var int
     */
    private $objectsCount;

    /**
     * @var string|null
     */
    private $errorMessage;

    /**
     * @var array
     */
    private $options;

    public function __construct(PagerInterface $pager, ObjectPersisterInterface $objectPersister, int $objectsCount, ?string $errorMessage, array $options)
    {
        $this->pager = $pager;
        $this->objectPersister = $objectPersister;
        $this->objectsCount = $objectsCount;
        $this->errorMessage = $errorMessage;
        $this->options = $options;
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

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getObjectsCount(): int
    {
        return $this->objectsCount;
    }
}
