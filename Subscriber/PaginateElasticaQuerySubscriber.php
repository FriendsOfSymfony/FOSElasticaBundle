<?php

namespace FOQ\ElasticaBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use FOQ\ElasticaBundle\Paginator\PaginatorAdapterInterface;

class PaginateElasticaQuerySubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event)
    {
        if ($event->target instanceof PaginatorAdapterInterface) {
            $results = $event->target->getResults($event->getOffset(),$event->getLimit());

            $event->count = $results->getTotalHits();
            $event->items = $results->toArray();

            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 1)
        );
    }
}