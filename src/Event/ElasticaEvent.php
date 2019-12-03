<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Symfony\Contracts\EventDispatcher\Event as BaseEvent;
use Symfony\Component\EventDispatcher\Event as LegacyBaseEvent;

if (class_exists(BaseEvent::class)) {
    class ElasticaEvent extends BaseEvent
    {
    }
} else {
    // Support Symfony 4.2 and before
    class ElasticaEvent extends LegacyBaseEvent
    {
    }
}
