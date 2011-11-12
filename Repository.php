<?php

namespace FOQ\ElasticaBundle;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * Basic respoitory to be extended to hold custom queries to be run
 * in the finder.
 */
class Repository
{
    protected $finder;

    public function __construct($finder)
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

}
