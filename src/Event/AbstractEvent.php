<?php

declare(strict_types=1);

namespace FOS\ElasticaBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\Event as LegacyEvent;

if (!class_exists(Event::class)) {
    /**
     * Symfony 3.4
     */
    abstract class AbstractEvent extends LegacyEvent
    {
    }
} else {
    /**
     * Symfony >= 4.3
     */
    abstract class AbstractEvent extends Event
    {
    }
}
