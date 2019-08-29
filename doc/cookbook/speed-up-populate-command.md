Speed up populate command
=========================

The bigger your project gets, the slower the populate command becomes.
There is a relation between amount of search data and the time you need to re-index it.  
Here's some signs, if you spot them consider using the solution from this tutorial:
  * The command takes enormous amount of time to re-index data.
  * It consumes a lot of memory.
  * It fails here and there because of lucking resources or running out of time.
  * It fails on some buggy model and you want to gracefully skip it and continue indexing.

This chapter describes a solution that improves the command performance and reduces its time&memory consumption. 
Instead of doing everything in one single process the populate command delegates work to workers (via [message queue](https://en.wikipedia.org/wiki/Message_queue)).
Those workers process small parts of the whole job and respond to the populate command with a status or error message.
The performance gain depends on how much workers (consumers) you run.

For queuing it uses [EnqueueBundle](https://github.com/php-enqueue/enqueue-dev/blob/master/docs/bundle/quick_tour.md) which supports a lot of MQ transports out of the box.

## Installation

I assume you already have `FOSElasticaBundle` installed, if not here's the [setup doc](../setup.md). 
So, we only have to install `EnqueueElasticaBundle` and one of the MQ transports. 
I am going to install the bundle and filesystem transport by way of example.

```bash
$ composer require enqueue/elastica-bundle:^0.8.1 enqueue/fs:^0.8
```

Default `enqueue.yaml`:
```enqueue:
    default:
        transport:
            dsn: '%env(resolve:ENQUEUE_DSN)%'
        client: ~
enqueue_elastica:
    transport: '%enqueue.default_transport%'
    doctrine: ~
```

_**Note:** As long as you are on Symfony Flex you are done. If not, you have to do some extra things, like registering the bundle in your `AppKernel` class._  
 
## Usage

* Run some consumers (the more you run the better performance you might get):

```bash
$ ./bin/console enqueue:consume --setup-broker -vvv 
```

or (use it only if you cannot use the solution above):

```bash
$ ./bin/console enqueue:transport:consume enqueue_elastica.populate_processor -vvv 
``` 
 
* Run populate command with `--pager-persiter=queue` option set:
 
```bash
$ ./bin/console fos:elastica:populate --pager-persister=queue 
```

## Customization

The `QueuePagerPersister` could be customized via options. 
The options could be customized in a listener subscribed on `FOS\ElasticaBundle\Persister\Event::PRE_PERSIST` event for example.

Here's the list of available options:

* `max_per_page` - Integer. Tells how many objects should be processed by a single worker at a time. 
* `first_page` - Integer. Tells from what page to start rebuilding the index.
* `last_page` - Integer. Tells on what page to stop rebuilding the index. 
* `populate_queue` - String. It is a name of a populate queue. Workers should consume messages from it.
* `populate_reply_queue` - String.  It is a name of a reply queue. The command should consume replies from it. Persister tries to create a temporary queue if not set.
* `reply_receive_timeout` - Float. A time a consumer waits for a message. In milliseconds.  
* `limit_overall_reply_time` - Int. Limits an overtime allowed processing time. Throws an exception if it is exceeded.

## Advices

* We suggest using [supervisord](https://github.com/php-enqueue/enqueue-dev/blob/master/docs/bundle/production_settings.md) on production to control consumers.

[back to index](../index.md)
