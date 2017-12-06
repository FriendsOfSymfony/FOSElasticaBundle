<?php
namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\Event;

final class PrePersistEvent extends Event
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
     * @param PagerInterface $pager
     */
    public function setPager($pager)
    {
        $this->pager = $pager;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return ObjectPersisterInterface
     */
    public function getObjectPersister()
    {
        return $this->objectPersister;
    }

    /**
     * @param ObjectPersisterInterface $objectPersister
     */
    public function setObjectPersister(ObjectPersisterInterface $objectPersister)
    {
        $this->objectPersister = $objectPersister;
    }
}
