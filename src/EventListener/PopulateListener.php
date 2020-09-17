<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\EventListener;

use FOS\ElasticaBundle\Event\PostIndexPopulateEvent;
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
     */
    public function __construct(Resetter $resetter)
    {
        $this->resetter = $resetter;
    }

    public function onPostIndexPopulate(PostIndexPopulateEvent $event): void
    {
        if (!$event->isReset()) {
            return;
        }

        $this->resetter->switchIndexAlias($event->getIndex(), $event->getOption('delete'));
    }
}
