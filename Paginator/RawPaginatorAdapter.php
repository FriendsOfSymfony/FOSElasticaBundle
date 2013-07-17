<?php

namespace FOS\ElasticaBundle\Paginator;

use Elastica\SearchableInterface;
use Elastica\Query;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\RawPartialResults;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @var integer the number of hits
     */
    private $totalHits = null;

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
        if ($this->query->hasParam('size') &&
            $this->query->getParam('size') < $offset + $itemCountPerPage) {
            $itemCountPerPage = $this->query->getParam('size') - $offset;
        }

        if ( 1 > $itemCountPerPage) {
            //the page exists without the size limit but should not be displayed due to the limit,
            throw new NotFoundHttpException('This page does not exist');
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
        if (null === $this->totalHits) {
            $totalHits = $this->searchable->search($this->query)->getTotalHits();
        } else {
            $totalHits = $this->totalHits;
        }

        if ($this->query->hasParam('size') &&
            $totalHits > $this->query->getParam('size')) {
            $totalHits = $this->query->getParam('size');
        }

        return $totalHits;
    }
}
