<?php

namespace FOS\ElasticaBundle\Finder;

use Elastica\Document;
use FOS\ElasticaBundle\Paginator\RawPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use Pagerfanta\Pagerfanta;
use Elastica\SearchableInterface;
use Elastica\Query;

/**
 * Finds elastica documents and map them to persisted objects
 */
class RawFinder implements PaginatedFinderInterface
{
    /**
     * @var SearchableInterface
     */
    protected $searchable;

    public function __construct(SearchableInterface $searchable)
    {
        $this->searchable  = $searchable;
    }

    /**
     * Search for a query string
     *
     * @param string $query
     * @param integer $limit
     * @param array $options
     * @return array of model objects
     **/
    public function find($query, $limit = null, $options = array())
    {
        return $this->search($query, $limit, $options);
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

        return $this->searchable->moreLikeThis($doc, $params, $query)->getResults();
    }

    /**
     * @param $query
     * @param null|int $limit
     * @param array $options
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
     * Gets a paginator wrapping the result of a search
     *
     * @param string $query
     * @param array $options
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

        return new RawPaginatorAdapter($this->searchable, $query, $options);
    }
}
