<?php
namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Provider\PagerInterface;

interface PagerPersisterInterface
{
    /**
     * @return void
     */
    public function insert(PagerInterface $pager, array $options = array());
}
