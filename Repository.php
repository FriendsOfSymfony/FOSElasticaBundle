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

    public function find($query, $limit=null)
    {
        return $this->finder->find($query, $limit);
    }

    public function findHybrid($query, $limit=null)
    {
        return $this->finder->findHybrid($query, $limit);
    }

    public function findPaginated($query)
    {
        return $this->finder->findPaginated($query);
    }

    public function createPaginatorAdapter($query)
    {
        return $this->finder->createPaginatorAdapter($query);
    }
}
