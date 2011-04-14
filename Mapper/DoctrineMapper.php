<?php

namespace FOQ\ElasticaBundle\Mapper;

use FOQ\ElasticaBundle\MapperInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class DoctrineMapper implements MapperInterface
{
    /**
     * Repository to fetch the objects from
     *
     * @var ObjectRepository
     */
    protected $objectRepository = null;

    /**
     * Doctrine identifier field
     *
     * @var string
     */
    protected $identifier = null;

    /**
     * Instanciates a new Mapper
     *
     * @param ObjectRepository objectRepository
     * @param string $identifier
     */
    public function __construct(ObjectRepository $objectRepository, $identifier = 'id')
    {
        $this->objectRepository = $objectRepository;
        $this->identifier       = $identifier;
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @return array
     **/
    public function fromElasticaObjects(array $elasticaObjects)
    {
        $ids = array_map(function($elasticaObject) {
            return $elasticaObject->getId();
        }, $elasticaObjects);

        return $this->objectRepository
            ->createQueryBuilder()
            ->field($this->identifier)->in($ids)
            ->getQuery()
            ->execute();
    }

}
