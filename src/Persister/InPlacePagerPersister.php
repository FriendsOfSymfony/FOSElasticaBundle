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

use FOS\ElasticaBundle\Persister\Event\OnExceptionEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PostPersistEvent;
use FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PrePersistEvent;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-import-type TPagerPersisterOptions from PagerPersisterInterface
 */
final class InPlacePagerPersister implements PagerPersisterInterface
{
    public const NAME = 'in_place';

    private PersisterRegistry $registry;
    private EventDispatcherInterface $dispatcher;

    public function __construct(PersisterRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(PagerInterface $pager, array $options = [])
    {
        $pager->setMaxPerPage(empty($options['max_per_page']) ? 100 : $options['max_per_page']);

        $options = \array_replace([
            'max_per_page' => $pager->getMaxPerPage(),
            'first_page' => $pager->getCurrentPage(),
            'last_page' => $pager->getNbPages(),
        ], $options);

        $pager->setCurrentPage($options['first_page']);

        $objectPersister = $this->registry->getPersister($options['indexName']);

        try {
            $this->dispatcher->dispatch($event = new PrePersistEvent($pager, $objectPersister, $options));
            $pager = $event->getPager();
            $options = $event->getOptions();

            $lastPage = \min($options['last_page'], $pager->getNbPages());
            $page = $pager->getCurrentPage();
            do {
                $pager->setCurrentPage($page);

                $this->insertPage($page, $pager, $objectPersister, $options);

                ++$page;
            } while ($page <= $lastPage);
        } finally {
            $this->dispatcher->dispatch(new PostPersistEvent($pager, $objectPersister, $options));
        }
    }

    /**
     * @phpstan-param TPagerPersisterOptions $options
     *
     * @throws \Exception
     */
    private function insertPage(int $page, PagerInterface $pager, ObjectPersisterInterface $objectPersister, array $options = []): void
    {
        $pager->setCurrentPage($page);

        $this->dispatcher->dispatch($event = new PreFetchObjectsEvent($pager, $objectPersister, $options));
        $pager = $event->getPager();
        $options = $event->getOptions();

        $objects = $pager->getCurrentPageResults();

        if ($objects instanceof \Traversable) {
            $objects = \iterator_to_array($objects);
        }

        $this->dispatcher->dispatch($event = new PreInsertObjectsEvent($pager, $objectPersister, $objects, $options));
        $pager = $event->getPager();
        $options = $event->getOptions();
        $objects = $event->getObjects();

        try {
            if (!empty($objects)) {
                $objectPersister->insertMany($objects);
            }

            $this->dispatcher->dispatch(new PostInsertObjectsEvent($pager, $objectPersister, $objects, $options));
        } catch (\Exception $e) {
            $this->dispatcher->dispatch($event = new OnExceptionEvent($pager, $objectPersister, $e, $objects, $options));

            if ($event->isIgnored()) {
                $this->dispatcher->dispatch(new PostInsertObjectsEvent($pager, $objectPersister, $objects, $options));
            } else {
                $e = $event->getException();

                throw $e;
            }
        }
    }
}
