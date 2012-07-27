<?php

namespace FOQ\ElasticaBundle\Finder;

use FOQ\ElasticaBundle\Finder\FinderInterface;
use FOQ\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOQ\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use FOQ\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use Pagerfanta\Pagerfanta;
use Elastica_Searchable;
use Elastica_Query;

/**
 * Finds elastica documents and map them to persisted objects
 */
class TransformedFinder implements PaginatedFinderInterface
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
     */
    public function findPaginated($query)
    {
        $queryObject = Elastica_Query::create($query);
        $paginatorAdapter = $this->createPaginatorAdapter($queryObject);

        return new Pagerfanta(new FantaPaginatorAdapter($paginatorAdapter));
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginatorAdapter($query)
    {
        $query = Elastica_Query::create($query);
        return new TransformedPaginatorAdapter($this->searchable, $query, $this->transformer);
    }
}
