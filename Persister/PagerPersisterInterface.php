<?php
namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Provider\PagerInterface;

interface PagerPersisterInterface
{
    /**
     * @param PagerInterface $pager
     * @param array $options
     *
     * @return void
     */
    public function insert(PagerInterface $pager, array $options = array());
}
