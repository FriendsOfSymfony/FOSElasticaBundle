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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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
        $sortField = $this->getRequest()->get($options['sortFieldParameterName']);

        if (!$sortField && isset($options['defaultSortFieldName'])) {
            $sortField = $options['defaultSortFieldName'];
        }

        if (!empty($sortField)) {
            $event->target->getQuery()->setSort([
                $sortField => $this->getSort($sortField, $options),
            ]);
        }
    }

    protected function getSort($sortField, array $options = [])
    {
        $sort = [
            'order' => $this->getSortDirection($sortField, $options),
        ];

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

    protected function getSortDirection($sortField, array $options = [])
    {
        $dir = 'asc';
        $sortDirection = $this->getRequest()->get($options['sortDirectionParameterName']);

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
     * @return Request|null
     */
    private function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'knp_pager.items' => ['items', 1],
        ];
    }
}
