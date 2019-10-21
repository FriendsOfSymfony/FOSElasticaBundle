<?php

namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Persister\Event\Events;
use FOS\ElasticaBundle\Persister\Event\OnExceptionEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PostPersistEvent;
use FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PrePersistEvent;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;

final class InPlacePagerPersister implements PagerPersisterInterface
{
    const NAME = 'in_place';

    /**
     * @var PersisterRegistry
     */
    private $registry;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param PersisterRegistry $registry
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(PersisterRegistry $registry, EventDispatcherInterface $dispatcher)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;

        if (class_exists(LegacyEventDispatcherProxy::class)) {
            $this->dispatcher = LegacyEventDispatcherProxy::decorate($dispatcher);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function insert(PagerInterface $pager, array $options = array())
    {
        $pager->setMaxPerPage(empty($options['max_per_page']) ? 100 : $options['max_per_page']);

        $options = array_replace([
            'max_per_page' => $pager->getMaxPerPage(),
            'first_page' => $pager->getCurrentPage(),
            'last_page' => $pager->getNbPages(),
        ], $options);

        $pager->setCurrentPage($options['first_page']);

        $objectPersister = $this->registry->getPersister($options['indexName'], $options['typeName']);

        try {
            $event = new PrePersistEvent($pager, $objectPersister, $options);
            $this->dispatcher->dispatch($event, Events::PRE_PERSIST);
            $pager = $event->getPager();
            $options = $event->getOptions();

            $lastPage = min($options['last_page'], $pager->getNbPages());
            $page = $pager->getCurrentPage();
            do {
                $pager->setCurrentPage($page);

                $this->insertPage($page, $pager, $objectPersister, $options);

                $page++;
            } while ($page <= $lastPage);
        } finally {
            $event = new PostPersistEvent($pager, $objectPersister, $options);
            $this->dispatcher->dispatch($event, Events::POST_PERSIST);
        }
    }

    /**
     * @param int $page
     * @param PagerInterface $pager
     * @param ObjectPersisterInterface $objectPersister
     * @param array $options
     *
     * @throws \Exception
     */
    private function insertPage($page, PagerInterface $pager, ObjectPersisterInterface $objectPersister, array $options = array())
    {
        $pager->setCurrentPage($page);

        $event = new PreFetchObjectsEvent($pager, $objectPersister, $options);
        $this->dispatcher->dispatch($event, Events::PRE_FETCH_OBJECTS);
        $pager = $event->getPager();
        $options = $event->getOptions();

        $objects = $pager->getCurrentPageResults();

        if ($objects instanceof \Traversable) {
            $objects = iterator_to_array($objects);
        }

        $event = new PreInsertObjectsEvent($pager, $objectPersister, $objects, $options);
        $this->dispatcher->dispatch($event, Events::PRE_INSERT_OBJECTS);
        $pager = $event->getPager();
        $options = $event->getOptions();
        $objects = $event->getObjects();

        try {
            if (!empty($objects)) {
                $objectPersister->insertMany($objects);
            }

            $event = new PostInsertObjectsEvent($pager, $objectPersister, $objects, $options);
            $this->dispatcher->dispatch($event, Events::POST_INSERT_OBJECTS);
        } catch (\Exception $e) {
            $event = new OnExceptionEvent($pager, $objectPersister, $e, $objects, $options);
            $this->dispatcher->dispatch($event, Events::ON_EXCEPTION);

            if ($event->isIgnored()) {
                $event = new PostInsertObjectsEvent($pager, $objectPersister, $objects, $options);
                $this->dispatcher->dispatch($event, Events::POST_INSERT_OBJECTS);
            } else {
                $e = $event->getException();

                throw $e;
            }
        }
    }

}
