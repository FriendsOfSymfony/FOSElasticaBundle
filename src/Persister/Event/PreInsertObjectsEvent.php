<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class PreInsertObjectsEvent extends Event implements PersistEvent
{
    /**
     * @var PagerInterface
     */
    private $pager;

    /**
     * @var ObjectPersisterInterface
     */
    private $objectPersister;

    /**
     * @var list<object>
     */
    private $objects;

    /**
     * @var array<string, mixed>
     */
    private $options;

    /**
     * @var int
     */
    private $filteredObjectCount = 0;

    /**
     * @param list<object>         $objects
     * @param array<string, mixed> $options
     */
    public function __construct(PagerInterface $pager, ObjectPersisterInterface $objectPersister, array $objects, array $options)
    {
        $this->pager = $pager;
        $this->objectPersister = $objectPersister;
        $this->objects = $objects;
        $this->options = $options;
    }

    public function getPager(): PagerInterface
    {
        return $this->pager;
    }

    /**
     * @return void
     */
    public function setPager(PagerInterface $pager)
    {
        $this->pager = $pager;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getObjectPersister(): ObjectPersisterInterface
    {
        return $this->objectPersister;
    }

    /**
     * @return void
     */
    public function setObjectPersister(ObjectPersisterInterface $objectPersister)
    {
        $this->objectPersister = $objectPersister;
    }

    /**
     * @return list<object>
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param list<object> $objects
     *
     * @return void
     */
    public function setObjects($objects)
    {
        $this->objects = $objects;
    }

    /**
     * @param int $count
     *
     * @return void
     *
     * @internal
     */
    public function setFilteredObjectCount($count)
    {
        $this->filteredObjectCount = $count;
    }

    /**
     * @return int
     */
    public function getFilteredObjectCount()
    {
        return $this->filteredObjectCount;
    }
}
