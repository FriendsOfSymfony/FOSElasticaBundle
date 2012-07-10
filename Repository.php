<?php

namespace FOQ\ElasticaBundle;

use FOQ\ElasticaBundle\Finder\PaginatedFinderInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Basic respoitory to be extended to hold custom queries to be run
 * in the finder.
 */
class Repository
{
    protected $finder;

    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }


    public function find($query)
    {
        return $this->finder->find($query);
    }

    public function findPaginated($query)
    {
        return $this->finder->findPaginated($query);
    }

    public function findHybrid($query)
    {
        return $this->finder->findHybrid($query);
    }

    public function createPaginatorAdapter($query)
    {
        return $this->finder->createPaginatorAdapter($query);
    }
}
