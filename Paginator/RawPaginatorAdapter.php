<?php

namespace FOS\ElasticaBundle\Paginator;

use Elastica\SearchableInterface;
use Elastica\Query;
use Elastica\ResultSet;
use InvalidArgumentException;

/**
 * Allows pagination of Elastica\Query. Does not map results.
 */
class RawPaginatorAdapter implements PaginatorAdapterInterface
{
    /**
     * @var SearchableInterface the object to search in
     */
    private $searchable;

    /**
     * @var Query the query to search
     */
    private $query;

    /**
     * @var array search options
     */
    private $options;

    /**
     * @var integer the number of hits
     */
    private $totalHits;

    /**
     * @var array for the aggregations
     */
    private $aggregations;

    /**
     * @var array for the suggesters
     */
    private $suggests;

    /**
     * @see PaginatorAdapterInterface::__construct
     *
     * @param SearchableInterface $searchable the object to search in
     * @param Query               $query      the query to search
     * @param array               $options
     */
    public function __construct(SearchableInterface $searchable, Query $query, array $options = array())
    {
        $this->searchable = $searchable;
        $this->query      = $query;
        $this->options    = $options;
    }

    /**
     * Returns the paginated results.
     *
     * @param integer $offset
     * @param integer $itemCountPerPage
     *
     * @throws \InvalidArgumentException
     *
     * @return ResultSet
     */
    protected function getElasticaResults($offset, $itemCountPerPage)
    {
        $offset = (integer) $offset;
        $itemCountPerPage = (integer) $itemCountPerPage;
        $size = $this->query->hasParam('size')
            ? (integer) $this->query->getParam('size')
            : null;

        if (null !== $size && $size < $offset + $itemCountPerPage) {
            $itemCountPerPage = $size - $offset;
        }

        if ($itemCountPerPage < 1) {
            throw new InvalidArgumentException('$itemCountPerPage must be greater than zero');
        }

        $query = clone $this->query;
        $query->setFrom($offset);
        $query->setSize($itemCountPerPage);

        $resultSet = $this->searchable->search($query, $this->options);
        $this->totalHits = $resultSet->getTotalHits();
        $this->aggregations = $resultSet->getAggregations();
        $this->suggests = $resultSet->getSuggests();

        return $resultSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($offset, $itemCountPerPage)
    {
        return new RawPartialResults($this->getElasticaResults($offset, $itemCountPerPage));
    }

    /**
     * Returns the number of results.
     *
     * If genuineTotal is provided as true, total hits is returned from the
     * hits.total value from the search results instead of just returning
     * the requested size.
     *
     * {@inheritdoc}
     */
    public function getTotalHits($genuineTotal = false)
    {
        if (! isset($this->totalHits)) {
            $this->totalHits = $this->searchable->count($this->query);
        }

        return $this->query->hasParam('size') && !$genuineTotal
            ? min($this->totalHits, (integer) $this->query->getParam('size'))
            : $this->totalHits;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        if (!isset($this->aggregations)) {
            $this->aggregations = $this->searchable->search($this->query)->getAggregations();
        }

        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuggests()
    {
        if (!isset($this->suggests)) {
            $this->suggests = $this->searchable->search($this->query)->getSuggests();
        }

        return $this->suggests;
    }

    /**
     * Returns the Query.
     *
     * @return Query the search query
     */
    public function getQuery()
    {
        return $this->query;
    }
}
