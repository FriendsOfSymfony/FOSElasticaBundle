<?php

declare(strict_types=1);

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
     */
    public function handlesObject(object $object): bool;

    /**
     * Insert one object into the type
     * The object will be transformed to an elastica document.
     */
    public function insertOne(object $object): void;

    /**
     * Replaces one object in the type.
     */
    public function replaceOne(object $object): void;

    /**
     * Deletes one object in the type.
     */
    public function deleteOne(object $object): void;

    /**
     * Deletes one object in the type by id.
     */
    public function deleteById(string $id, string|bool $routing = false): void;

    /**
     * Bulk inserts an array of objects in the type.
     *
     * @param list<object> $objects array of domain model objects
     */
    public function insertMany(array $objects): void;

    /**
     * Bulk updates an array of objects in the type.
     *
     * @param list<object> $objects array of domain model objects
     */
    public function replaceMany(array $objects): void;

    /**
     * Bulk deletes an array of objects in the type.
     *
     * @param list<object> $objects array of domain model objects
     */
    public function deleteMany(array $objects): void;

    /**
     * Bulk deletes records from an array of identifiers.
     *
     * @param list<string> $identifiers array of domain model object identifiers
     * @param string|bool  $routing     optional routing key for all identifiers
     */
    public function deleteManyByIdentifiers(array $identifiers, string|bool $routing = false): void;
}
