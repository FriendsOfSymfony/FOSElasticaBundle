<?php

namespace FOS\ElasticaBundle\Doctrine\ORM;

use Doctrine\Common\EventArgs;
use FOS\ElasticaBundle\Doctrine\AbstractListener;

class Listener extends AbstractListener
{
    public function postPersist(EventArgs $eventArgs)
    {
        parent::postPersist($eventArgs);
    }

    public function postUpdate(EventArgs $eventArgs)
    {
        parent::postUpdate($eventArgs);
    }

    public function preRemove(EventArgs $eventArgs)
    {
        parent::preRemove($eventArgs);
    }

    public function postRemove(EventArgs $eventArgs)
    {
        parent::postRemove($eventArgs);
    }
}
