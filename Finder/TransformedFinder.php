<?php

namespace FOQ\ElasticaBundle\Finder;

use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOQ\ElasticaBundle\Paginator\DoctrinePaginatorAdapter;
use Zend\Paginator\Paginator;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Finds elastica documents and map them to persisted objects
 */
class TransformedFinder implements FinderInterface, PaginatedFinderInterface
{
    protected $searchable;
    protected $transformer;

    public function __construct(Elastica_Searchable $searchable, ElasticaToModelTransformerInterface $transformer)
    {
        $this->searchable  = $searchable;
        $this->transformer = $transformer;
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
		$paginator = new Paginator($paginatorAdapter);

		return $paginator;
    }

    /**
     * Creates a paginator adapter for this query
     *
     * @param Elastica_Query $query
     * @return DoctrinePaginatorAdapter
     */
    protected function createPaginatorAdapter(Elastica_Query $query)
    {
		return new DoctrinePaginatorAdapter($this->searchable, $query, $this->transformer);
    }
}
