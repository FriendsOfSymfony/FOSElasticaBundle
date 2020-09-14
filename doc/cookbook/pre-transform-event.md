##### Pre transform event

Since FOSElasticaBundle 3.2.0, we now dispatch an event before an object is
transformed into an Elastica document. It allows you to do some necessary
operation before indexing.

For example, you have a backoffice which is exclusively used in a certain locale.
When you save objects which have translation, you have to index objects in
several indices (one per locale supported). It is necessary to reload data before
transforming to document with the good locale if it is not already done.

You can even manipulate empty Elastica document created in
`FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer` and fields
concerned by index process.

Set up an event listener or subscriber for 
`FOS\ElasticaBundle\Event\PreTransformEvent` to be able to do some
operation on your objects.

```php

namespace AcmeBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\ElasticaBundle\Event\PreTransformEvent;

class PreTransformListener implements EventSubscriberInterface
{
    private $anotherService;
    
    // ...
    
    public function doPreTransform(PreTransformEvent $event)
    {
        $this->anotherService->reloadTranslation($event->getObject());
    }
    
    public static function getSubscribedEvents()
    {
        return [
            PreTransformEvent::class => 'doPreTransform',
        ];
    }
}
```

Service definition (when autoconfigure is disabled):
```yml
acme.listener.custom_property:
    class: AcmeBundle\EventListener\PreTransformListener
    tags:
        - { name: kernel.event_subscriber }
```
