<?php

namespace FOS\ElasticaBundle\Paginator;

use Elastica\SearchableInterface;
use Elastica\Query;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\RawPartialResults;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;

/**
 * Allows pagination of Elastica\Query. Does not map results
 */
class RawPaginatorAdapter implements PaginatorAdapterInterface
{
    /**
     * @var SearchableInterface the object to search in
     */
    private $searchable = null;

    /**
     * @var Query the query to search
     */
    private $query = null;

    /**
     * @see PaginatorAdapterInterface::__construct
     *
     * @param SearchableInterface $searchable the object to search in
     * @param Query $query the query to search
     */
    public function __construct(SearchableInterface $searchable, Query $query)
    {
        $this->searchable = $searchable;
        $this->query      = $query;
    }

    /**
     * Returns the paginated results.
     *
     * @param $offset
     * @param $itemCountPerPage
     * @return ResultSet
     */
    protected function getElasticaResults($offset, $itemCountPerPage)
    {
        $query = clone $this->query;
        $query->setFrom($offset);
        $query->setLimit($itemCountPerPage);

        return $this->searchable->search($query);
    }

    /**
     * Returns the paginated results.
     *
     * @param int $offset
     * @param int $itemCountPerPage
     * @return PartialResultsInterface
     */
    public function getResults($offset, $itemCountPerPage)
    {
        return new RawPartialResults($this->getElasticaResults($offset, $itemCountPerPage));
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getTotalHits()
    {
        return $this->searchable->search($this->query)->getTotalHits();
    }
}
