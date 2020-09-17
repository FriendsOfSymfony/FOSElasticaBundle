<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

/**
 * Inserts, replaces and deletes single documents in an elastica type
 * Accepts domain model objects and converts them to elastica documents.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface ObjectPersisterInterface
{
    /**
     * Checks if this persister can handle the given object or not.
     *
     * @param mixed $object
     */
    public function handlesObject($object): bool;

    /**
     * Insert one object into the type
     * The object will be transformed to an elastica document.
     *
     * @param object $object
     */
    public function insertOne($object);

    /**
     * Replaces one object in the type.
     *
     * @param object $object
     **/
    public function replaceOne($object);

    /**
     * Deletes one object in the type.
     *
     * @param object $object
     **/
    public function deleteOne($object);

    /**
     * Deletes one object in the type by id.
     *
     * @param mixed       $id
     * @param string|bool $routing
     */
    public function deleteById($id, $routing = false);

    /**
     * Bulk inserts an array of objects in the type.
     *
     * @param array $objects array of domain model objects
     */
    public function insertMany(array $objects);

    /**
     * Bulk updates an array of objects in the type.
     *
     * @param array $objects array of domain model objects
     */
    public function replaceMany(array $objects);

    /**
     * Bulk deletes an array of objects in the type.
     *
     * @param array $objects array of domain model objects
     */
    public function deleteMany(array $objects);

    /**
     * Bulk deletes records from an array of identifiers.
     *
     * @param array       $identifiers array of domain model object identifiers
     * @param string|bool $routing     optional routing key for all identifiers
     */
    public function deleteManyByIdentifiers(array $identifiers, $routing = false);
}
