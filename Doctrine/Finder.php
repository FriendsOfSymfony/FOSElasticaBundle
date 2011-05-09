<?php

namespace FOQ\ElasticaBundle\Doctrine;

use FOQ\ElasticaBundle\Finder\FinderInterface;
use FOQ\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Zend\Paginator\Paginator;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Finds elastica documents and map them to persisted objects
 */
class Finder implements FinderInterface, PaginatedFinderInterface
{
    protected $searchable;
    protected $transformer;

    public function __construct(Elastica_Searchable $searchable, ElasticaToModelTransformerInterface $transformer)
    {
        $this->searchable  = $searchable;
        $this->transformer = $transformer;
    }

    /**
     * Search for a query string
     *
     * @return array of model objects
     **/
    public function find($query, $limit)
    {
		$queryObject = Elastica_Query::create($query);
        $queryObject->setLimit($limit);
        $results = $this->searchable->search($queryObject)->getResults();

        return $this->transformer->transform($results);
    }

    /**
     * Gets a paginator wrapping the result of a search
     *
     * @return Paginator
     **/
    public function findPaginated($query)
    {
		$queryObject = Elastica_Query::create($query);
        $results = $this->searchable->search($queryObject)->getResults();
		$paginatorAdapter = $this->createPaginatorAdapter($queryObject);

		return new Paginator($paginatorAdapter);
    }

    /**
     * Creates a paginator adapter for this query
     *
     * @param Elastica_Query $query
     * @return DoctrinePaginatorAdapter
     */
    protected function createPaginatorAdapter(Elastica_Query $query)
    {
		return new PaginatorAdapter($this->searchable, $query, $this->transformer);
    }
}
