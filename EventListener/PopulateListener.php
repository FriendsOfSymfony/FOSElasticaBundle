<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\EventListener;

use FOS\ElasticaBundle\Event\IndexPopulateEvent;
use FOS\ElasticaBundle\Index\Resetter;

/**
 * PopulateListener.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class PopulateListener
{
    /**
     * @var Resetter
     */
    private $resetter;

    /**
     * PopulateListener constructor.
     *
     * @param Resetter $resetter
     */
    public function __construct(Resetter $resetter)
    {
        $this->resetter = $resetter;
    }

    /**
     * @param IndexPopulateEvent $event
     */
    public function onPostIndexPopulate(IndexPopulateEvent $event)
    {
        if (!$event->isReset()) {
            return;
        }
        $this->resetter->switchIndexAlias($event->getIndex(), $event->getOption('delete'));
    }
}
