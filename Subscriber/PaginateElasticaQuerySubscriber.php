<?php

namespace FOS\ElasticaBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginateElasticaQuerySubscriber implements EventSubscriberInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function items(ItemsEvent $event)
    {
        if ($event->target instanceof PaginatorAdapterInterface) {
            // Add sort to query
            $this->setSorting($event);

            /** @var $results PartialResultsInterface */
            $results = $event->target->getResults($event->getOffset(), $event->getLimit());

            $event->count = $results->getTotalHits();
            $event->items = $results->toArray();
            $facets = $results->getFacets();
            if (null != $facets) {
                $event->setCustomPaginationParameter('facets', $facets);
            }
            $aggregations = $results->getAggregations();
            if (null != $aggregations) {
                $event->setCustomPaginationParameter('aggregations', $aggregations);
            }

            $event->stopPropagation();
        }
    }

    /**
     * Adds knp paging sort to query.
     *
     * @param ItemsEvent $event
     */
    protected function setSorting(ItemsEvent $event)
    {
        $options = $event->options;
        $request = $this->requestStack->getCurrentRequest();

        $sortField = $request->get($options['sortFieldParameterName']);
        if (empty($sortField)) {
            return;
        }

        // determine sort direction
        $dir = 'asc';
        $sortDirection = $request->get($options['sortDirectionParameterName']);
        if ('desc' === strtolower($sortDirection)) {
            $dir = 'desc';
        }

        // check if the requested sort field is in the sort whitelist
        if (isset($options['sortFieldWhitelist']) && !in_array($sortField, $options['sortFieldWhitelist'])) {
            throw new \UnexpectedValueException(sprintf('Cannot sort by: [%s] this field is not in whitelist', $sortField));
        }

        // set sort on active query
        $event->target->getQuery()->setSort(array(
            $sortField => array('order' => $dir),
        ));
    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 1),
        );
    }
}
