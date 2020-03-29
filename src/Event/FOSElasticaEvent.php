<?php

namespace FOS\ElasticaBundle\Event;

use Symfony\Component\EventDispatcher\Event as ComponentEvent;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;

if (class_exists(ContractsEvent::class)) {
    abstract class FOSElasticaEvent extends ContractsEvent
    {

    }
} else {
    abstract class FOSElasticaEvent extends ComponentEvent
    {

    }
}
