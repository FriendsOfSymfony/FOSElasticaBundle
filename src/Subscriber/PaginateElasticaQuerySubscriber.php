<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
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
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return void
     */
    public function items(ItemsEvent $event)
    {
        if ($event->target instanceof PaginatorAdapterInterface) {
            // Add sort to query
            $this->setSorting($event);

            /** @var PartialResultsInterface $results */
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

    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.items' => ['items', 1],
        ];
    }

    /**
     * Adds knp paging sort to query.
     *
     * @return void
     */
    protected function setSorting(ItemsEvent $event)
    {
        $options = $event->options ?? [];
        $sortField = $this->getFromRequest($options['sortFieldParameterName'] ?? null);

        if (!$sortField && isset($options['defaultSortFieldName'])) {
            $sortField = $options['defaultSortFieldName'];
        }

        if (!empty($sortField)) {
            $event->target->getQuery()->setSort([
                $sortField => $this->getSort($sortField, $options),
            ]);
        }
    }

    /**
     * @param string               $sortField
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function getSort($sortField, array $options = [])
    {
        $sort = [
            'order' => $this->getSortDirection($sortField, $options),
        ];

        if (isset($options['sortNestedPath'])) {
            $path = \is_callable($options['sortNestedPath']) ?
                $options['sortNestedPath']($sortField) : $options['sortNestedPath'];

            if (!empty($path)) {
                $sort['nested_path'] = $path;
            }
        }

        if (isset($options['sortNestedFilter'])) {
            $filter = \is_callable($options['sortNestedFilter']) ?
                $options['sortNestedFilter']($sortField) : $options['sortNestedFilter'];

            if (!empty($filter)) {
                $sort['nested_filter'] = $filter;
            }
        }

        return $sort;
    }

    /**
     * @param string               $sortField
     * @param array<string, mixed> $options
     *
     * @return string
     */
    protected function getSortDirection($sortField, array $options = [])
    {
        $dir = 'asc';
        $sortDirection = $this->getFromRequest($options['sortDirectionParameterName']);

        if (empty($sortDirection) && isset($options['defaultSortDirection'])) {
            $sortDirection = $options['defaultSortDirection'];
        }

        if (null !== $sortDirection && 'desc' === \strtolower($sortDirection)) {
            $dir = 'desc';
        }

        // check if the requested sort field is in the sort whitelist
        if (isset($options['sortFieldAllowList']) && !\in_array($sortField, $options['sortFieldAllowList'], true)) {
            throw new \UnexpectedValueException(\sprintf('Cannot sort by: [%s] this field is not in whitelist', $sortField));
        }

        return $dir;
    }

    private function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return mixed|null
     */
    private function getFromRequest(?string $key)
    {
        if (null !== $key && null !== $request = $this->getRequest()) {
            return $request->get($key);
        }

        return null;
    }
}
