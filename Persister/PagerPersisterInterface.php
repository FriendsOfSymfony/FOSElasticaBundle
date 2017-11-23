<?php
namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Provider\PagerInterface;

interface PagerPersisterInterface
{
    /**
     * @param PagerInterface $pager
     * @param \Closure|null $loggerClosure
     * @param array $options
     *
     * @return void
     */
    public function insert(PagerInterface $pager, \Closure $loggerClosure = null, array $options = array());
}
