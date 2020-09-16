<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister\Listener;

use FOS\ElasticaBundle\Persister\Event\PreInsertObjectsEvent;
use FOS\ElasticaBundle\Provider\IndexableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilterObjectsListener implements EventSubscriberInterface
{
    /**
     * @var IndexableInterface
     */
    private $indexable;

    public function __construct(IndexableInterface $indexable)
    {
        $this->indexable = $indexable;
    }

    public function filterObjects(PreInsertObjectsEvent $event): void
    {
        $options = $event->getOptions();
        if (false == empty($options['skip_indexable_check'])) {
            return;
        }

        $objects = $event->getObjects();
        $index = $options['indexName'];

        $filtered = [];
        foreach ($objects as $object) {
            if (!$this->indexable->isObjectIndexable($index, $object)) {
                continue;
            }

            $filtered[] = $object;
        }

        $event->setObjects($filtered);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PreInsertObjectsEvent::class => 'filterObjects',
        ];
    }
}
