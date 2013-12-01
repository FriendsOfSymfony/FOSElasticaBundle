<?php

namespace FOS\ElasticaBundle\Finder;

use Elastica\Document;
use Elastica\ResultSet;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use Pagerfanta\Pagerfanta;
use Elastica\SearchableInterface;
use Elastica\Query;

/**
 * Finds elastica documents and map them to persisted objects
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

    /**
     * Transform a given \Elastica\ResultSet
     *
     * @param \Elastica\ResultSet $resultSet
     * @return array of model objects
     */
    public function transform(ResultSet $resultSet)
    {
        return $this->transformer->transform($resultSet->getResults());
    }

    /**
     * @param \Elastica\ResultSet $resultSet
     * @return array of model objects
     */
    public function hybridTransform(ResultSet $resultSet)
    {
        return $this->transformer->transform($resultSet->getResults());
    }

    public function findHybrid($query, $limit = null)
    {
        $results = $this->search($query, $limit);

        return $this->transformer->hybridTransform($results);
    }

    /**
     * Find documents similar to one with passed id.
     *
     * @param integer $id
     * @param array $params
     * @param array $query
     * @return array of model objects
     **/
    public function moreLikeThis($id, $params = array(), $query = array())
    {
        $doc = new Document($id);
        $results = $this->searchable->moreLikeThis($doc, $params, $query)->getResults();

        return $this->transformer->transform($results);
    }

    /**
     * @param $query
     * @param null|int $limit
     * @return array
     */
    protected function search($query, $limit = null)
    {
        $queryObject = Query::create($query);
        if (null !== $limit) {
            $queryObject->setSize($limit);
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
        $queryObject = Query::create($query);
        $paginatorAdapter = $this->createPaginatorAdapter($queryObject);

        return new Pagerfanta(new FantaPaginatorAdapter($paginatorAdapter));
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginatorAdapter($query)
    {
        $query = Query::create($query);
        return new TransformedPaginatorAdapter($this->searchable, $query, $this->transformer);
    }
}
