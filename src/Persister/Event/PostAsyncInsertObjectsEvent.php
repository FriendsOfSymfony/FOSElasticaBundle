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

    public function __construct(PagerInterface $pager, ObjectPersisterInterface $objectPersister, $objectsCount, $errorMessage, array $options)
    {
        $this->pager = $pager;
        $this->objectPersister = $objectPersister;
        $this->objectsCount = $objectsCount;
        $this->errorMessage = $errorMessage;
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
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return int
     */
    public function getObjectsCount()
    {
        return $this->objectsCount;
    }
}