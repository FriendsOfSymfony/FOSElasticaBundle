<?php

namespace FOQ\ElasticaBundle\Persister;

/**
 * Inserts, replaces and deletes single documents in an elastica type
 * Accepts domain model objects and converts them to elastica documents
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface ObjectPersisterInterface
{
    /**
     * Insert one object into the type
     * The object will be transformed to an elastica document
     *
     * @param object $object
     */
    function insertOne($object);

    /**
     * Replaces one object in the type
     *
     * @param object $object
     **/
    function replaceOne($object);

    /**
     * Deletes one object in the type
     *
     * @param object $object
     **/
    function deleteOne($object);

    /**
     * Inserts an array of objects in the type
     *
     * @param array of domain model objects
     **/
    function insertMany(array $objects);
}
