<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use FOS\ElasticaBundle\Persister\Event\PersistEvent;
use FOS\ElasticaBundle\Persister\Event\PostInsertObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreFetchObjectsEvent;
use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RegisterListenersService
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function register(ObjectManager $manager, PagerInterface $pager, array $options)
    {
        $options = array_replace([
            'clear_object_manager' => true,
            'debug_logging' => false,
            'sleep' => 0,
        ], $options);

        if ($options['clear_object_manager']) {
            $this->addListener($pager, PostInsertObjectsEvent::class, function () use ($manager) {
                $manager->clear();
            });
        }

        if ($options['sleep']) {
            $this->addListener($pager, PostInsertObjectsEvent::class, function () use ($options) {
                usleep($options['sleep']);
            });
        }

        if (false == $options['debug_logging'] && $manager instanceof EntityManagerInterface) {
            $configuration = $manager->getConnection()->getConfiguration();
            $logger = $configuration->getSQLLogger();

            $this->addListener($pager, PreFetchObjectsEvent::class, function () use ($configuration) {
                $configuration->setSQLLogger(null);
            });

            $this->addListener($pager, PreInsertObjectsEvent::class, function () use ($configuration, $logger) {
                $configuration->setSQLLogger($logger);
            });
        }
    }

    /**
     * @param string $eventName
     */
    private function addListener(PagerInterface $pager, $eventName, \Closure $callable)
    {
        $this->dispatcher->addListener($eventName, function (PersistEvent $event) use ($pager, $callable) {
            if ($event->getPager() !== $pager) {
                return;
            }

            call_user_func_array($callable, func_get_args());
        });
    }
}
