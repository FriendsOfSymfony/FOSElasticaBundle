<?php

namespace FOQ\ElasticaBundle\Finder;

use FOQ\ElasticaBundle\Paginator\RawPaginatorAdapter;
use FOQ\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use Pagerfanta\Pagerfanta;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Finds elastica documents
 */
class RawFinder implements FinderInterface, PaginatedFinderInterface
{
    protected $searchable;

    public function __construct(Elastica_Searchable $searchable)
    {
        $this->searchable = $searchable;
    }

    /**
     * Search for a query string
     *
     * @return array of elastica objects
     **/
    public function find($query, $limit)
    {
        $queryObject = Elastica_Query::create($query);
        $queryObject->setLimit($limit);

        return $this->searchable->search($queryObject)->getResults();
    }

    /**
     * Gets a paginator wrapping the result of a search
     *
     * @return Pagerfanta
     **/
    public function findPaginated($query)
    {
        $queryObject = Elastica_Query::create($query);
        $paginatorAdapter = $this->createPaginatorAdapter($queryObject);

        return new Pagerfanta(new FantaPaginatorAdapter($paginatorAdapter));
    }

    /**
     * Creates a paginator adapter for this query
     *
     * @param Elastica_Query $query
     *
     * @return RawPaginatorAdapter
     */
    public function createPaginatorAdapter(Elastica_Query $query)
    {
        return new RawPaginatorAdapter($this->searchable, $query);
    }
}
