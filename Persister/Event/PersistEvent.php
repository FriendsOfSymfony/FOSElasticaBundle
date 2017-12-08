<?php
namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;

interface PersistEvent
{
    /**
     * @return PagerInterface
     */
    public function getPager();

    /**
     * @return array
     */
    public function getOptions();
    
    /**
     * @return ObjectPersisterInterface
     */
    public function getObjectPersister();
}
