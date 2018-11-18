Speed up populate command with AWS SQS
======================================

## Setup

Prerequisites here are
- AWS AIM user that has access to the SQS resources, here called `acme_aim_user`
- AWS SQS queues created, called `acme_fos_elastica_populate` and `acme_fos_elastica_populate_reply_queue`

Both queues can be created by the AWS GUI, or other methods.

Install `enqueue/elastica-bundle@dev-master` and `enqueue/sqs`

```bash
$ composer require enqueue/elastica-bundle@dev-master
$ composer require enqueue/sqs
```

Define a handful of useful entries in `parameters.yml`

```yaml
parameters:
    aws_sqs_key: XXXXX
    aws_sqs_secret_key: XXXXX
    aws_sqs_region: XXXXX
    aws_sqs_queue_name: XXXXX
    aws_sqs_reply_queue_name: XXXXX
```

Your `vendor/enqueue.yml` config should look like, taking three of the parameters defined above.

```yaml
enqueue:
    transport:
        default: sqs
        sqs:
            key: "%aws_sqs_key%"
            secret: "%aws_sqs_secret_key%"
            region: "%aws_sqs_region%"

enqueue_elastica:
    doctrine: ~
```

Add a new block to your `services.yml`, passing the other 2 parameters to it.

```yaml
services:
    AppBundle\Listener\QueuePagerPersister:
        public: true
        arguments:
            $queueName: "%aws_sqs_queue_name%"
            $replyQueueName: "%aws_sqs_reply_queue_name%"
        tags:
            - { name: kernel.event_listener, event: elastica.pager_persister.pre_persist, method: prePersist }
```

Here is a simple implementation of `QueuePagerPersister` that is aware of AWS, again it takes the parameters you defined above.

```php
namespace AppBundle\Listener;

use FOS\ElasticaBundle\Persister\Event\PrePersistEvent;

/**
 * Specifies AWS SQS queue name.
 */
class QueuePagerPersister
{
    /** @var string AWS SQS queue name */
    protected $queueName;

    /** @var string AWS SQS reply queue name */
    protected $replyQueueName;

    /**
     * Constructor.
     *
     * @param string $queueName
     */
    public function __construct(string $queueName, string $replyQueueName)
    {
        $this->queueName = $queueName;
        $this->replyQueueName = $replyQueueName;
    }

    /**
     * Specifies AWS SQS queue name.
     *
     * @param PrePersistEvent $event
     */
    public function prePersist(PrePersistEvent $event)
    {
        $options = $event->getOptions();
        $options['populate_queue'] = $this->queueName;
        $options['populate_reply_queue'] = $this->replyQueueName;
        $event->setOptions($options);
    }
}
```

*Note* You can use `var_dump()` calls in this listener to confirm that your options are getting passed correctly.

Now you should be ready to start pushing the massages into the AWS SQS queue, this is the output you should be seeing.

```bash
php bin/console fos:elastica:populate --pager-persister=queue -vvv
Resetting app
```

This command will hang, and not return, until the queue messages are processed.
If you put in `var_dump()` in prePersist, you should see some of the options passed into the queue.

Go the AWS GUI console and you should see the messages added to the queue. If you poll for messages they should look something like

```json
{
  "options": {
    "max_per_page": 1000,
    "delete": true,
    "reset": true,
    "ignore_errors": false,
    "sleep": 0,
    "indexName": "app",
    "typeName": "user"
  },
  "page": 1
}
```

The messages are now in the AWS SQS queue, ready to be consumed.

```bash
php bin/console enqueue:transport:consume enqueue_elastica.populate_processor --queue=acme_fos_elastica_populate -vv
[info] Start consuming
[info] Message received from the queue: acme_fos_elastica_populate
[info] Message processed: enqueue.ack
[info] Message received from the queue: acme_fos_elastica_populate
[info] Message processed: enqueue.ack
...
```

You should now start seeing the progress increase with the fos:elastica:populate, when all the messages are consumed you should get a brand new and re-populated index.

```bash
Populating
Populating
 1/1 [============================] 100% < 1 sec/< 1 sec 70.0 MiB
Populating
    0 [>---------------------------] < 1 sec 70.0 MiBRefreshing app
Refreshing app
```

Here you're using a single process to digest the messages. It is possible to greatly speed-up this operation by spawning multiple processes, see the section on `supervisord` below.

## Advices

* We suggest using [supervisord](https://github.com/php-enqueue/enqueue-dev/blob/master/docs/bundle/production_settings.md) on production to control consumers.

[back to index](../index.md)
