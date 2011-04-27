<?php

namespace FOQ\ElasticaBundle\Transformer;

use Doctrine\Common\Persistence\ObjectManager;
use Elastica_Document;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids
 */
class ElasticaToModelDoctrineTransformer implements ElasticaToModelTransformerInterface
{
    /**
     * Repository to fetch the objects from
     *
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * Class of the model to map to the elastica documents
     *
     * @var string
     */
    protected $objectClass = null;

    /**
     * Optional parameters
     *
     * @var array
     */
    protected $options = array(
        'hydrate'    => true,
		'identifier' => 'id'
    );

    /**
     * Instanciates a new Mapper
     *
     * @param ObjectManager objectManager
     * @param string $objectClass
     * @param array $options
     */
    public function __construct(ObjectManager $objectManager, $objectClass, array $options = array())
    {
        $this->objectManager = $objectManager;
        $this->objectClass   = $objectClass;
        $this->options       = array_merge($this->options, $options);
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository
     *
     * @param array of elastica objects
     * @return array
     **/
    public function transform(array $elasticaObjects)
    {
        $ids = array_map(function($elasticaObject) {
            return $elasticaObject->getId();
        }, $elasticaObjects);

        $objects = $this->objectManager
            ->createQueryBuilder($this->objectClass)
            ->field($this->options['identifier'])->in($ids)
            ->hydrate($this->options['hydrate'])
            ->getQuery()
            ->execute()
            ->toArray();

		$identifierGetter = 'get'.ucfirst($this->options['identifier']);

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        usort($objects, function($a, $b) use ($idPos, $identifierGetter)
        {
            return $idPos[$a->$identifierGetter()] > $idPos[$b->$identifierGetter()];
        });

        return $objects;
    }
}
