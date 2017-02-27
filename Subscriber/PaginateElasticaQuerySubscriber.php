<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Subscriber;

use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginateElasticaQuerySubscriber implements EventSubscriberInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param RequestStack|Request $requestStack
     */
    public function setRequest($requestStack)
    {
        if ($requestStack instanceof Request) {
            $this->request = $requestStack;
        } elseif ($requestStack instanceof RequestStack) {
            $this->request = $requestStack->getMasterRequest();
        }
    }

    /**
     * @param ItemsEvent $event
     */
    public function items(ItemsEvent $event)
    {
        if ($event->target instanceof PaginatorAdapterInterface) {
            // Add sort to query
            $this->setSorting($event);

            /** @var $results PartialResultsInterface */
            $results = $event->target->getResults($event->getOffset(), $event->getLimit());

            $event->count = $results->getTotalHits();
            $event->items = $results->toArray();
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
        $sortField = $this->request->get($options['sortFieldParameterName']);

        if (!$sortField && isset($options['defaultSortFieldName'])) {
            $sortField = $options['defaultSortFieldName'];
        }

        if (!empty($sortField)) {
            $event->target->getQuery()->setSort(array(
                $sortField => $this->getSort($sortField, $options),
            ));
        }
    }

    protected function getSort($sortField, array $options = array())
    {
        $sort = array(
            'order' => $this->getSortDirection($sortField, $options),
        );

        if (isset($options['sortNestedPath'])) {
            $path = is_callable($options['sortNestedPath']) ?
                $options['sortNestedPath']($sortField) : $options['sortNestedPath'];

            if (!empty($path)) {
                $sort['nested_path'] = $path;
            }
        }

        if (isset($options['sortNestedFilter'])) {
            $filter = is_callable($options['sortNestedFilter']) ?
                $options['sortNestedFilter']($sortField) : $options['sortNestedFilter'];

            if (!empty($filter)) {
                $sort['nested_filter'] = $filter;
            }
        }

        return $sort;
    }

    protected function getSortDirection($sortField, array $options = array())
    {
        $dir = 'asc';
        $sortDirection = $this->request->get($options['sortDirectionParameterName']);

        if (empty($sortDirection) && isset($options['defaultSortDirection'])) {
            $sortDirection = $options['defaultSortDirection'];
        }

        if ('desc' === strtolower($sortDirection)) {
            $dir = 'desc';
        }

        // check if the requested sort field is in the sort whitelist
        if (isset($options['sortFieldWhitelist']) && !in_array($sortField, $options['sortFieldWhitelist'])) {
            throw new \UnexpectedValueException(sprintf('Cannot sort by: [%s] this field is not in whitelist', $sortField));
        }

        return $dir;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 1),
        );
    }
}
