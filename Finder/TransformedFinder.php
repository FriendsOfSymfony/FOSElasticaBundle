<?php

namespace FOS\ElasticaBundle\Finder;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
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

    /**
     * @param $query
     * @param null|int $limit
     * @return array
     */
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
     * @param string $query
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
