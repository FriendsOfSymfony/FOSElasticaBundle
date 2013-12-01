<?php

namespace FOS\ElasticaBundle\Doctrine\MongoDB;

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

    public function postRemove(EventArgs $eventArgs)
    {
        parent::postRemove($eventArgs);
    }
}
