<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\EventDispatcher;

use FOS\ElasticaBundle\Event\ElasticaEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;
use TypeError;

final class LegacyEventDispatcherProxy
{
    public static function decorate(?EventDispatcherInterface $dispatcher): ?EventDispatcherInterface
    {
        if ($dispatcher === null) {
            return null;
        }

        if (!$dispatcher instanceof ContractsEventDispatcherInterface) {
            return $dispatcher;
        }

        return new class($dispatcher) implements EventDispatcherInterface {
            /**
             * @var EventDispatcherInterface
             */
            private $dispatcher;

            /**
             * @param EventDispatcherInterface $dispatcher
             */
            public function __construct(EventDispatcherInterface $dispatcher)
            {
                $this->dispatcher = $dispatcher;
            }

            /**
             * {@inheritdoc}
             */
            public function dispatch($eventName/*, object $event = null*/)
            {
                $event = 1 < func_num_args() ? func_get_arg(1) : new ElasticaEvent();

                if (!is_string($eventName)) {
                    throw new TypeError(sprintf('Argument 1 passed to "%s::dispatch()" must be a string, %s given.', EventDispatcherInterface::class, is_object($eventName) ? get_class($eventName) : gettype($eventName)));
                }

                $this->dispatcher->dispatch($event, $eventName);
            }

            /**
             * {@inheritdoc}
             */
            public function addListener($eventName, $listener, $priority = 0)
            {
                return $this->dispatcher->addListener($eventName, $listener, $priority);
            }

            /**
             * {@inheritdoc}
             */
            public function addSubscriber(EventSubscriberInterface $subscriber)
            {
                return $this->dispatcher->addSubscriber($subscriber);
            }

            /**
             * {@inheritdoc}
             */
            public function removeListener($eventName, $listener)
            {
                return $this->dispatcher->removeListener($eventName, $listener);
            }

            /**
             * {@inheritdoc}
             */
            public function removeSubscriber(EventSubscriberInterface $subscriber)
            {
                return $this->dispatcher->removeSubscriber($subscriber);
            }

            /**
             * {@inheritdoc}
             */
            public function getListeners($eventName = null): array
            {
                return $this->dispatcher->getListeners($eventName);
            }

            /**
             * {@inheritdoc}
             */
            public function getListenerPriority($eventName, $listener): ?int
            {
                return $this->dispatcher->getListenerPriority($eventName, $listener);
            }

            /**
             * {@inheritdoc}
             */
            public function hasListeners($eventName = null): bool
            {
                return $this->dispatcher->hasListeners($eventName);
            }

            /**
             * Proxies all method calls to the original event dispatcher.
             */
            public function __call($method, $arguments)
            {
                return $this->dispatcher->{$method}(...$arguments);
            }
        };
    }
}
