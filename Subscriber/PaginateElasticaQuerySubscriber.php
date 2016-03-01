<?php

namespace FOS\ElasticaBundle\Subscriber;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginateElasticaQuerySubscriber implements EventSubscriberInterface
{
    /**
     * @var Request|null
     */
    private $request;

    /**
     * @var RequestStack|null
     */
    private $requestStack;

    /**
     * @param Request|null $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param RequestStack|null $requestStack
     */
    public function setRequestStack(RequestStack $requestStack = null)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return Request|null
     */
    private function getRequest()
    {
        if ($this->requestStack instanceof RequestStack) {
            return $this->requestStack->getMasterRequest();
        } elseif ($this->request instanceof Request) {
            return $this->request;
        } else {
            return null;
        }
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
        $request = $this->getRequest();

        $options = $event->options;
        $sortField = $request instanceof Request ? $request->get($options['sortFieldParameterName']) : null;

        if (!empty($sortField)) {
            // determine sort direction
            $dir = 'asc';
            $sortDirection = $request instanceof Request ? $request->get($options['sortDirectionParameterName']) : null;
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
    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 1),
        );
    }
}
