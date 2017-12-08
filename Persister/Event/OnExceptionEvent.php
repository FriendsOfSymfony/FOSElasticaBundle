<?php
namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\Event;

final class OnExceptionEvent extends Event
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

    private $ignore;

    public function __construct(PagerInterface $pager, ObjectPersisterInterface $objectPersister, \Exception $exception, array $options)
    {
        $this->pager = $pager;
        $this->objectPersister = $objectPersister;
        $this->exception = $exception;
        $this->options = $options;

        $this->ignore = false;
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
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return bool
     */
    public function isIgnored()
    {
        return $this->ignore;
    }

    /**
     * @param bool $ignore
     */
    public function setIgnore($ignore)
    {
        $this->ignore = !!$ignore;
    }
}