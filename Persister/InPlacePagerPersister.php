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

final class InPlacePagerPersister implements PagerPersisterInterface
{
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
    }

    /**
     * {@inheritdoc}
     */
    public function insert(PagerInterface $pager, array $options = array())
    {
        $objectPersister = $this->registry->getPersister($options['indexName'], $options['typeName']);

        $event = new PrePersistEvent($pager, $objectPersister, $options);
        $this->dispatcher->dispatch(Events::PRE_PERSIST, $event);
        $pager = $event->getPager();
        $options = $event->getOptions();

        $pager->setMaxPerPage($options['batch_size']);

        $page = $pager->getCurrentPage();
        while ($page <= $pager->getNbPages()) {
            try {
                $pager->setCurrentPage($page);

                $event = new PreFetchObjectsEvent($pager, $objectPersister, $options);
                $this->dispatcher->dispatch(Events::PRE_FETCH_OBJECTS, $event);
                $pager = $event->getPager();
                $options = $event->getOptions();

                $objects = $pager->getCurrentPageResults();

                $event = new PreInsertObjectsEvent($pager, $objectPersister, $objects, $options);
                $this->dispatcher->dispatch(Events::PRE_INSERT_OBJECTS, $event);
                $pager = $event->getPager();
                $options = $event->getOptions();
                $objects = $event->getObjects();

                if (!empty($objects)) {
                    $objectPersister->insertMany($objects);
                }

                $event = new PostInsertObjectsEvent($pager, $objectPersister, $objects, $options);
                $this->dispatcher->dispatch(Events::POST_INSERT_OBJECTS, $event);
            } catch (\Exception $e) {
                $event = new OnExceptionEvent($pager, $objectPersister, $e, $options);
                $this->dispatcher->dispatch(Events::ON_EXCEPTION, $event);

                if (false == $event->isIgnored()) {
                    $e = $event->getException();

                    throw $e;
                }
            }

            $pager->setCurrentPage($page++);
        }

        $event = new PostPersistEvent($pager, $objectPersister, $options);
        $this->dispatcher->dispatch(Events::POST_PERSIST, $event);

    }
}
