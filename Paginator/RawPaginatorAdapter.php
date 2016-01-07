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
     * @var array for the facets
     */
    private $facets;

    /**
     * @var array for the aggregations
     */
    private $aggregations;

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

        if (method_exists($resultSet, 'getFacets')) {
            $this->facets = $resultSet->getFacets();
        }
        
        $this->aggregations = $resultSet->getAggregations();

        return $resultSet;
    }

    /**
     * Returns the paginated results.
     *
     * @param int $offset
     * @param int $itemCountPerPage
     *
     * @return PartialResultsInterface
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
     * @param boolean $genuineTotal
     *
     * @return integer The number of results.
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
     * Returns Facets.
     *
     * @return mixed
     */
    public function getFacets()
    {
        if (! isset($this->facets)) {
            $this->facets = $this->searchable->search($this->query)->getFacets();
        }

        return $this->facets;
    }

    /**
     * Returns Aggregations.
     *
     * @return mixed
     */
    public function getAggregations()
    {
        if (!isset($this->aggregations)) {
            $this->aggregations = $this->searchable->search($this->query)->getAggregations();
        }

        return $this->aggregations;
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
