<?php

namespace FOS\ElasticaBundle;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Basic repository to be extended to hold custom queries to be run
 * in the finder.
 */
class Repository
{
    protected $finder;

    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param mixed   $query
     * @param integer $limit
     * @param array   $options
     *
     * @return array
     */
    public function find($query, $limit = null, $options = array())
    {
        return $this->finder->find($query, $limit, $options);
    }

    /**
     * @param mixed   $query
     * @param integer $limit
     * @param array   $options
     *
     * @return mixed
     */
    public function findHybrid($query, $limit = null, $options = array())
    {
        return $this->finder->findHybrid($query, $limit, $options);
    }

    /**
     * @param mixed $query
     * @param array $options
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function findPaginated($query, $options = array())
    {
        return $this->finder->findPaginated($query, $options);
    }

    /**
     * @param string $query
     * @param array  $options
     *
     * @return Paginator\PaginatorAdapterInterface
     */
    public function createPaginatorAdapter($query, $options = array())
    {
        return $this->finder->createPaginatorAdapter($query, $options);
    }
}
