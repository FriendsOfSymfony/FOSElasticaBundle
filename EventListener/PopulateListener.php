<?php
/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) FriendsOfSymfony <https://github.com/FriendsOfSymfony/FOSElasticaBundle/graphs/contributors>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\EventListener;

use FOS\ElasticaBundle\Event\PopulateEvent;
use FOS\ElasticaBundle\Index\Resetter;

/**
 * PopulateListener
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
     * @param Resetter $resetter
     */
    public function __construct(Resetter $resetter)
    {
        $this->resetter = $resetter;
    }

    /**
     * @param PopulateEvent $event
     */
    public function preIndexPopulate(PopulateEvent $event)
    {
        if (!$event->isReset()) {
            return;
        }

        if (null !== $event->getType()) {
            $this->resetter->resetIndexType($event->getIndex(), $event->getType());
        } else {
            $this->resetter->resetIndex($event->getIndex(), true);
        }
    }

    /**
     * @param PopulateEvent $event
     */
    public function postIndexPopulate(PopulateEvent $event)
    {
        $this->resetter->postPopulate($event->getIndex());
    }
} 
