<?php

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Type\LookupInterface;

abstract class AbstractLookup implements LookupInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }
}
