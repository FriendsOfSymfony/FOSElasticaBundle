<?php

namespace FOQ\ElasticaBundle\Propel;

use FOQ\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Elastica_Document;

/**
 * Maps Elastica documents with Propel objects
 * This mapper assumes an exact match between
 * elastica documents ids and propel object ids
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class ElasticaToModelTransformer implements ElasticaToModelTransformerInterface
{
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
     * Instantiates a new Mapper
     *
     * @param string $objectClass
     * @param array $options
     */
    public function __construct($objectClass, array $options = array())
    {
        $this->objectClass = $objectClass;
        $this->options     = array_merge($this->options, $options);
    }

    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the propel repository
     *
     * @param array of elastica objects
     * @return array
     **/
    public function transform(array $elasticaObjects)
    {
        $ids = array_map(function($elasticaObject) {
            return $elasticaObject->getId();
        }, $elasticaObjects);

        $objects = $this->findByIdentifiers($this->objectClass, $this->options['identifier'], $ids, $this->options['hydrate']);

        $identifierGetter = 'get'.ucfirst($this->options['identifier']);

        // sort objects in the order of ids
        $idPos = array_flip($ids);
        $objects->uasort(function($a, $b) use ($idPos, $identifierGetter) {
            return $idPos[$a->$identifierGetter()] > $idPos[$b->$identifierGetter()];
        });

        return $objects;
    }


    /**
     * Fetch objects for theses identifier values
     *
     * @param string $class the model class
     * @param string $identifierField like 'id'
     * @param array $identifierValues ids values
     * @param Boolean $hydrate whether or not to hydrate the objects, false returns arrays
     * @return array of objects or arrays
     */
    protected function findByIdentifiers($class, $identifierField, array $identifierValues, $hydrate)
    {
        if (empty($identifierValues)) {
            return array();
        }

        $queryClass   = $class.'Query';
        $filterMethod = 'filterBy'.$this->camelize($identifierField);
        $query = $queryClass::create()
            ->$filterMethod($identifierValues)
            ;

        if (!$hydrate) {
            return $query->toArray();
        }

        return $query->find();
    }

    /**
     * @see https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Util/Inflector.php
     */
    private function camelize($str)
    {
        return ucfirst(str_replace(" ", "", ucwords(strtr($str, "_-", "  "))));
    }
}
