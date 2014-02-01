<?php

namespace FOS\ElasticaBundle\Persister;

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
     * Deletes one object in the type by id
     *
     * @param mixed $id
     *
     * @return null
     */
    function deleteById($id);

    /**
     * Bulk inserts an array of objects in the type
     *
     * @param array $objects array of domain model objects
     */
    function insertMany(array $objects);

    /**
     * Bulk updates an array of objects in the type
     *
     * @param array $objects array of domain model objects
     */
    function replaceMany(array $objects);

    /**
     * Bulk deletes an array of objects in the type
     *
     * @param array $objects array of domain model objects
     */
    function deleteMany(array $objects);
}
