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

    public function find($query, $limit = null, $options = array())
    {
        return $this->finder->find($query, $limit, $options);
    }

    public function findHybrid($query, $limit = null, $options = array())
    {
        return $this->finder->findHybrid($query, $limit, $options);
    }

    public function findPaginated($query, $options = array())
    {
        return $this->finder->findPaginated($query, $options);
    }

    public function createPaginatorAdapter($query, $options = array())
    {
        return $this->finder->createPaginatorAdapter($query, $options);
    }
}
