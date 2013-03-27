<?php

namespace FOS\ElasticaBundle\Finder;

use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use Pagerfanta\Pagerfanta;
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
     * Search for a query string
     *
     * @param string $query
     * @param integer $limit
     * @return array of model objects
     **/
    public function find($query, $limit = null)
    {
        $results = $this->search($query, $limit);

        return $this->transformer->transform($results);
    }

    public function findHybrid($query, $limit = null)
    {
        $results = $this->search($query, $limit);

        return $this->transformer->hybridTransform($results);
    }

    protected function search($query, $limit = null)
    {
        $queryObject = Elastica_Query::create($query);
        if (null !== $limit) {
            $queryObject->setLimit($limit);
        }
        $results = $this->searchable->search($queryObject)->getResults();

        return $results;
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

        return new Pagerfanta($paginatorAdapter);
    }

    /**
     * Creates a paginator adapter for this query
     *
     * @param Elastica_Query $query
     * @return TransformedPaginatorAdapter
     */
    protected function createPaginatorAdapter(Elastica_Query $query)
    {
        return new TransformedPaginatorAdapter($this->searchable, $query, $this->transformer);
    }
}
