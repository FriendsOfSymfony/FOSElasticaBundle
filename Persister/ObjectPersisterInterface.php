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
    const BULK_INSERT = 'addDocuments';
    const BULK_REPLACE = 'updateDocuments';

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
     **/
    function deleteById($id);

    /**
     * Bulk update an array of objects in the type for the given method
     *
     * @param array $objects array of domain model objects
     * @param string Method to call
     */
    function bulkPersist(array $objects, $method);
}
