##### Custom Properties

Since FOSElasticaBundle 3.1.0, we now dispatch an event for each transformation of an 
object into an Elastica document which allows you to set custom properties on the Elastica
document for indexing.

Set up an event listener or subscriber for 
`FOS\ElasticaBundle\Event\TransformEvent::POST_TRANSFORM` to be able to inject your own
parameters.

```php
class CustomPropertyListener implements EventSubscriberInterface
{
    private $anotherService;
    
    // ...
    
    public function addCustomProperty(TransformEvent $event)
    {
        $document = $event->getDocument();
        $custom = $this->anotherService->calculateCustom($event->getObject());
                    
        $document->set('custom', $custom);
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            TransformEvent::POST_TRANSFORM => 'addCustomProperty',
        );
    }
}
```
