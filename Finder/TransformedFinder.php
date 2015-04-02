<?php

namespace FOS\ElasticaBundle\Finder;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use Pagerfanta\Pagerfanta;
use Elastica\SearchableInterface;
use Elastica\Query;

/**
 * Finds elastica documents and map them to persisted objects.
 */
class TransformedFinder implements PaginatedFinderInterface
{
    protected $searchable;
    protected $transformer;

    public function __construct(SearchableInterface $searchable, ElasticaToModelTransformerInterface $transformer)
    {
        $this->searchable  = $searchable;
        $this->transformer = $transformer;
    }

    /**
     * Search for a query string.
     *
     * @param string  $query
     * @param integer $limit
     * @param array   $options
     * @param array   $transformOptions
     *
     * @return array of model objects
     **/
    public function find($query, $limit = null, $options = array(), $transformOptions = array())
    {
        $results = $this->search($query, $limit, $options);

        return $this->transformer->transform($results, $transformOptions);
    }

    public function findHybrid($query, $limit = null, $options = array(), $transformOptions = array())
    {
        $results = $this->search($query, $limit, $options);

        return $this->transformer->hybridTransform($results, $transformOptions);
    }

    /**
     * Find documents similar to one with passed id.
     *
     * @param integer $id
     * @param array   $params
     * @param array   $query
     * @param array   $transformOptions
     *
     * @return array of model objects
     **/
    public function moreLikeThis($id, $params = array(), $query = array(), $transformOptions = array())
    {
        $doc = new Document($id);
        $results = $this->searchable->moreLikeThis($doc, $params, $query)->getResults();

        return $this->transformer->transform($results, $transformOptions);
    }

    /**
     * @param $query
     * @param null|int $limit
     * @param array    $options
     *
     * @return array
     */
    protected function search($query, $limit = null, $options = array())
    {
        $queryObject = Query::create($query);
        if (null !== $limit) {
            $queryObject->setSize($limit);
        }
        $results = $this->searchable->search($queryObject, $options)->getResults();

        return $results;
    }

    /**
     * Gets a paginator wrapping the result of a search.
     *
     * @param string $query
     * @param array  $options
     *
     * @return Pagerfanta
     */
    public function findPaginated($query, $options = array())
    {
        $queryObject = Query::create($query);
        $paginatorAdapter = $this->createPaginatorAdapter($queryObject, $options);

        return new Pagerfanta(new FantaPaginatorAdapter($paginatorAdapter));
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginatorAdapter($query, $options = array())
    {
        $query = Query::create($query);

        return new TransformedPaginatorAdapter($this->searchable, $query, $options, $this->transformer);
    }
}
