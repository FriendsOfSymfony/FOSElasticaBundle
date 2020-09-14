Populate Events
===============

Often when you need import large amount data and keep performance of your storage,
you'll use [bulk insert](https://en.wikipedia.org/wiki/Bulk_insert) and Elasticsearch is not an exception.
But in case of production Elasticsearch, you may suffer of high load on data-node,
because Elasticsearch performs async refresh of shard(s).

According to [official documentation about bulk indexing](https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-update-settings.html#bulk)
to squeeze out more performance out of bulk indexing, you must set `refresh_interval` to `-1` by executing:

```bash
curl -XPUT localhost:9200/test/_settings -d '{
    "index" : {
        "refresh_interval" : "-1"
    } }'
```

Then, once bulk indexing is done, settings can be updated (back to default)

```bash
curl -XPUT localhost:9200/test/_settings -d '{
    "index" : {
        "refresh_interval" : "1s"
    } }'
```

And _forcemerge should be called:

```bash
curl -XPOST 'http://localhost:9200/test/_forcemerge?max_num_segments=5'
```

Everything seems to be straightforward, but you'll how this can be achieved with FOSElasticaBundle?
For this purpose `PreIndexPopulateEvent` and `PostIndexPopulateEvent` were introduced, they allow you to monitor and hook into the process.

Now let's implement `PopulateListener` by creating `AppBundle\EventListener\PopulateListener`:

```php
<?php
namespace AppBundle\EventListener;

use FOS\ElasticaBundle\Event\PostIndexPopulateEvent;
use FOS\ElasticaBundle\Event\PreIndexPopulateEvent;
use FOS\ElasticaBundle\Index\IndexManager;

class PopulateListener
{
    /**
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @param IndexManager $indexManager
     */
    public function __construct(IndexManager $indexManager)
    {
        $this->indexManager    = $indexManager;
    }

    public function preIndexPopulate(PreIndexPopulateEvent $event)
    {
        $index = $this->indexManager->getIndex($event->getIndex());
        $settings = $index->getSettings();
        $settings->setRefreshInterval(-1);
    }

    public function postIndexPopulate(PostIndexPopulateEvent $event)
    {
        $index = $this->indexManager->getIndex($event->getIndex());
        $settings = $index->getSettings();
        $index->optimize(['max_num_segments' => 5]);
        $settings->setRefreshInterval('1s');
    }
}
```

Note : for Elasticsearch v2.1 and above, you will need to "manually" call `_forcemerge` :

```php
...
    public function postIndexPopulate(PostIndexPopulateEvent $event)
    {
        $index = $this->indexManager->getIndex($event->getIndex());
        $index->getClient()->request('_forcemerge', 'POST', ['max_num_segments' => 5]);
        $index->getSettings()->setRefreshInterval(Settings::DEFAULT_REFRESH_INTERVAL);
    }
...
```

Declare your listener and register event(s):

```xml
<service id="app.event_listener.populate_listener" class="AppBundle\EventListener\PopulateListener">
    <tag name="kernel.event_listener" event="FOS\ElasticaBundle\Event\PreIndexPopulateEvent" method="preIndexPopulate"/>
    <tag name="kernel.event_listener" event="FOS\ElasticaBundle\Event\PostIndexPopulateEvent" method="postIndexPopulate"/>
    <argument type="service" id="fos_elastica.index_manager"/>
</service>
```

and pretty much that's it!

Other events that were introduced: `PreIndexResetEvent` and `PostIndexResetEvent` are working in similar way.
