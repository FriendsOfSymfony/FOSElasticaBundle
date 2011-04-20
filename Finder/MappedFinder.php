<?php

namespace FOQ\ElasticaBundle\Finder;

use FOQ\ElasticaBundle\MapperInterface;
use FOQ\ElasticaBundle\Paginator\DoctrinePaginatorAdapter;
use Zend\Paginator\Paginator;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Finds elastica documents and map them to persisted objects
 */
class MappedFinder implements FinderInterface, PaginatedFinderInterface
{
    protected $searchable;
    protected $mapper;

    public function __construct(Elastica_Searchable $searchable, MapperInterface $mapper)
    {
        $this->searchable = $searchable;
        $this->mapper     = $mapper;
    }

    /**
     * Search for a query string in the food searchable
     *
     * @return array of Food documents
     **/
    public function find($query, $limit)
    {
		$queryObject = Elastica_Query::create($query);
        $queryObject->setLimit($limit);
        $results = $this->searchable->search($queryObject)->getResults();

        return $this->mapper->fromElasticaObjects($results);
    }

    /**
     * Gets a paginator wrapping the result of a search
     *
     * @return Paginator
     **/
    public function findPaginated($query)
    {
		$queryObject = Elastica_Query::create($query);
		$paginatorAdapter = $this->createPaginatorAdapter($queryObject);
		$paginator = new Paginator($paginatorAdapter);

		return $this->createPaginator($queryObject);
    }

    /**
     * Creates a paginator adapter for this query
     *
     * @param Elastica_Query $query
     * @return DoctrinePaginatorAdapter
     */
    protected function createPaginatorAdapter(Elastica_Query $query)
    {
		return new DoctrinePaginatorAdapter($this->searchable, $query, $this->mapper);
    }
}
