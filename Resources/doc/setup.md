Step 1: Setting up the bundle
=============================

A) Install FOSElasticaBundle
----------------------------

FOSElasticaBundle is installed using [Composer](https://getcomposer.org).

```bash
$ php composer.phar require friendsofsymfony/elastica-bundle "~3.0"
```

### Elasticsearch

Instructions for installing and deploying Elasticsearch may be found
[here](http://www.elasticsearch.org/guide/reference/setup/installation/).


B) Enable FOSElasticaBundle
---------------------------

Enable FOSElasticaBundle in your AppKernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\ElasticaBundle\FOSElasticaBundle(),
    );
}
```

C) Basic Bundle Configuration
-----------------------------

The basic minimal configuration for FOSElasticaBundle is one client with one Elasticsearch
index. In almost all cases, an application will only need a single index. An index can
be considered comparable to a Doctrine Entity Manager, where the index will hold multiple
type definitions.

```yaml
#app/config/config.yml
fos_elastica:
    clients:
        default: { host: localhost, port: 9200 }
    indexes:
        app: ~
```

In this example, an Elastica index (an instance of `Elastica\Index`) is available as a
service with the key `fos_elastica.index.app`.

You may want the index `app` to be named something else on ElasticSearch depending on
if your application is running in a different env or other conditions that suit your
application. To set your customer index to a name that depends on the environment of your
Symfony application, use the example below:

```yaml
#app/config/config.yml
fos_elastica:
    indexes:
        app:
            index_name: app_%kernel.environment%
```

In this case, the service `fos_elastica.index.app` will relate to an ElasticSearch index
that varies depending on your kernel's environment. For example, in dev it will relate to
`app_dev`.

D) Defining index types
-----------------------

By default, FOSElasticaBundle requires each type that is to be indexed to be mapped.
It is possible to use a serializer to avoid this requirement. To use a serializer, see
the [serializer documentation](serializer.md)

An Elasticsearch type needs to be defined with each field of a related PHP object that
will end up being indexed.

```yaml
fos_elastica:
    indexes:
        app:
            types:
                user:
                    mappings:
                        username: ~
                        firstName: ~
                        lastName: ~
                        email: ~
```

Each defined type is made available as a service, and in this case the service key is
`fos_elastica.index.app.user` and is an instance of `Elastica\Type`.

FOSElasticaBundle requires a provider for each type that will notify when an object
that maps to a type has been modified. The bundle ships with support for Doctrine and
Propel objects.

Below is an example for the Doctrine ORM.

```yaml
                user:
                    mappings:
                        username: ~
                        firstName: ~
                        lastName: ~
                        email: ~
                    persistence:
                        # the driver can be orm, mongodb or propel
                        # listener and finder are not supported by
                        # propel and should be removed
                        driver: orm
                        model: Acme\ApplicationBundle\Entity\User
                        provider: ~
                        listener:
                            immediate: ~
                        finder: ~
```

There are a significant number of options available for types, that can be
[found here](types.md)

E) Populating the Elasticsearch index
-------------------------------------

When using the providers and listeners that come with the bundle, any new or modified
object will be indexed automatically. In some cases, where the database is modified
externally, the Elasticsearch index must be updated manually. This can be achieved by
running the console command:

```bash
$ php app/console fos:elastica:populate
```

The command will also create all indexes and types defined if they do not already exist
on the Elasticsearch server.

F) Usage
--------

Usage documentation for the bundle is available [here](usage.md)
