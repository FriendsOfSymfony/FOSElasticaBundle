# Doctrine queue listener

FOSElasticaBundle subscribes on Doctrine events, such as insert, update, remove to adjust the index accordingly.
The listener might start consuming more and more resources, most importantly time of http response.
Or, Sometimes it fails, bringing the whole your app down too, because of ElasticSearch server is out of order or some bug in the code.
Keep reading if you want to improve http response time or strive for better fault tolerance.

Instead of doing everything in one single process the listener just sends a message to a worker (via [message queue](https://en.wikipedia.org/wiki/Message_queue)).
The work does the actual synchronization job in background. 
For queuing it uses [EnqueueBundle](https://github.com/php-enqueue/enqueue-dev/blob/master/docs/bundle/quick_tour.md) which supports a lot of MQ transports out of the box.

## Installation

I assume you already have `FOSElasticaBundle` installed, if not here's the [setup doc](../setup.md). 
So, we only have to install `EnqueueElasticaBundle` and one of the MQ transports. 
I am going to install the bundle and filesystem transport by way of example.

```bash
$ composer require enqueue/elastica-bundle:^0.8.1 enqueue/fs:^0.8
```

_**Note:** As long as you are on Symfony Flex you are done. If not, you have to do some extra things, like registering the bundle in your `AppKernel` class._  
 
## Usage 

The usage is simple, you have to disable the default listener:

```yaml
fos_elastica:
    indexes:
        enqueue:
            types:
                blog:
                    persistence:
                        driver: orm
                        model: AppBundle\Entity\Blog
                        listener: { enabled: false }
```

and enable the queue one:

```
enqueue_elastica:
    doctrine:
        queue_listeners:
            -
              index_name: 'enqueue'
              type_name: 'blog'
              model_class: 'AppBundle\Entity\Blog'
```

Don't forget to run some queue consumers (the more you run the better performance you might get):

```bash
$ ./bin/console enqueue:consume --setup-broker -vvv 
```

or (use it only if you cannot use the solution above):

```bash
$ ./bin/console enqueue:transport:consume enqueue_elastica.doctrine.sync_index_with_object_change_processor -vvv 
```

[back to index](../index.md)
