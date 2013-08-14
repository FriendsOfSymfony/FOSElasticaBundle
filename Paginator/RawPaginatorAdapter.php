<?php

namespace FOS\ElasticaBundle\Paginator;

use Elastica\SearchableInterface;
use Elastica\Query;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\RawPartialResults;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use InvalidArgumentException;

/**
 * Allows pagination of Elastica\Query. Does not map results
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
     * @var integer the number of hits
     */
    private $totalHits;

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
        $offset = (integer) $offset;
        $itemCountPerPage = (integer) $itemCountPerPage;
        $size = $this->query->hasParam('size')
            ? (integer) $this->query->getParam('size')
            : null;

        if ($size && $size < $offset + $itemCountPerPage) {
            $itemCountPerPage = $size - $offset;
        }

        if ($itemCountPerPage < 1) {
            throw new InvalidArgumentException('$itemCountPerPage must be greater than zero');
        }

        $query = clone $this->query;
        $query->setFrom($offset);
        $query->setSize($itemCountPerPage);

        $resultSet = $this->searchable->search($query);
        $this->totalHits = $resultSet->getTotalHits();

        return $resultSet;
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
        if ( ! isset($this->totalHits)) {
            $this->totalHits = $this->searchable->search($this->query)->getTotalHits();
        }

        return $this->query->hasParam('size')
            ? min($this->totalHits, (integer) $this->query->getParam('size'))
            : $this->totalHits;
    }
}
