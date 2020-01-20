<?php

declare(strict_types=1);

namespace FOS\ElasticaBundle\Event;

use Symfony\Component\EventDispatcher\Event as LegacyEvent;
use Symfony\Contracts\EventDispatcher\Event;

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
