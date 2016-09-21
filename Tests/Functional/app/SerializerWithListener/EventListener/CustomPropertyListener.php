<?php

namespace FOS\ElasticaBundle\Tests\Functional\app\SerializerWithListener\EventListener;


use Elastica\Document;
use FOS\ElasticaBundle\Event\TransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Giovanni Albero <giovannialbero.solinf@gmail.com>
 */
class CustomPropertyListener implements EventSubscriberInterface
{
    public function addCustomProperty(TransformEvent $event)
    {
        /** @var Document $document */
        $document = $event->getDocument();

        $data = $document->getData();

        if (is_string($data)) {
            $unserializeData = json_decode($data, true);
            $unserializeData['field1'] = 'post_persister';
            $document->setData(json_encode($unserializeData));
        }
    }

    public static function getSubscribedEvents()
    {
        return array (
            TransformEvent::POST_TRANSFORM => 'addCustomProperty',
        );
    }
}