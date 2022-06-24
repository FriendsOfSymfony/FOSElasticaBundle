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

final class PreFetchObjectsEvent extends Event implements PersistEvent
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
     * @var array<string, mixed>
     */
    private $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(PagerInterface $pager, ObjectPersisterInterface $objectPersister, array $options)
    {
        $this->pager = $pager;
        $this->objectPersister = $objectPersister;
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
}
