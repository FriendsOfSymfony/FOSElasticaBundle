##### Custom Properties

Since FOSElasticaBundle 3.1.0, we now dispatch an event for each transformation of an 
object into an Elastica document which allows you to set custom properties on the Elastica
document for indexing.

Set up an event listener or subscriber for 
`FOS\ElasticaBundle\Event\PostTransformEvent` to be able to inject your own
parameters.

```php

namespace AcmeBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\ElasticaBundle\Event\PostTransformEvent;

class CustomPropertyListener implements EventSubscriberInterface
{
    private $anotherService;
    
    // ...
    
    public function addCustomProperty(PostTransformEvent $event)
    {
        $document = $event->getDocument();
        $custom = $this->anotherService->calculateCustom($event->getObject());
                    
        $document->set('custom', $custom);
    }
    
    public static function getSubscribedEvents()
    {
        return [
            PostTransformEvent::class => 'addCustomProperty',
        ];
    }
}
```

Service definition (when autoconfigure is disabled):
```yml
acme.listener.custom_property:
    class: AcmeBundle\EventListener\CustomPropertyListener
    tags:
        - { name: kernel.event_subscriber }
```
