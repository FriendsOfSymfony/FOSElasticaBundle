Populate optimizations
======================

Here in this chapter we describe some ways to improve performance of `fos:elastica:populate` command. 

Enqueue Elastica Bundle
-----------------------

The solution utilize [message queue](https://en.wikipedia.org/wiki/Message_queue) by distributing the work among several consumers.
You can use different transports such as RabbitMQ, Amqp, Stomp, Filesystem and so.
The performance gain depends on how much consumers you run. 

More in the [bundle's doc](https://github.com/php-enqueue/enqueue-elastica-bundle).

[back to index](../index.md)
