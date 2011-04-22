[Elastica](https://github.com/ruflin/Elastica) integration in Symfony2

## Installation

### Install elasticsearch

http://www.elasticsearch.org/guide/reference/setup/installation.html

### Install Elastica

#### Download

With submodule:
    git submodule add git://github.com/ruflin/Elastica vendor/elastica

With clone:
    git clone git://github.com/ruflin/Elastica vendor/elastica

#### Register autoloading

    // app/autoload.php

    $loader->registerPrefixes(array(
        ...
        'Elastica' => __DIR__.'/../vendor/elastica/lib',
    ));

### Install ElasticaBundle

#### Download

With submodule:
    git submodule add git://github.com/Exercise/ElasticaBundle src/Exercise/ElasticaBundle

With clone:
    git clone git://github.com/Exercise/ElasticaBundle src/Exercise/ElasticaBundle

#### Register autoloading

    // app/autoload.php

    $loader->registerNamespaces(array(
        ...
        'FOQ' => __DIR__.'/../src',
    ));

#### Register the bundle

    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            // ...
            new FOQ\ElasticaBundle\FOQElasticaBundle(),
            // ...
        );
    }

### Basic configuration

#### Declare a client 

Elasticsearch client is comparable to doctrine connection.
Most of the time, you will need only one.

    #app/config/config.yml
    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }

#### Declare an index

Elasticsearch index is comparable to doctrine entity manager.
Most of the time, you will need only one.

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        indexes:
            website:
                client: default

Here we created a "website" index, that uses our "default" client.

#### Declare a type

Elasticsearch type is comparable to doctrine entity repository.

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        indexes:
            website:
                client: default
                types:
                    user:
                        mappings:
                            username: { boost: 5 }
                            firstName: { boost: 3 }
                            lastName: { boost: 3 }
                            aboutMe: 

### Populate the types

    php app/console foq:elastica:populate

This command needs providers to insert new documents in the elasticsearch types.
There are 2 ways to create providers.
If your elasticsearch type matches a doctrine repository, go for the doctrine automatic provider.
Or, for complete flexibility, go for explicit provider.

#### Doctrine automatic provider

If we want to index the entities from a doctrine repository,
a little bit a configuration will let ElasticaBundle do it for us.

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        indexes:
            website:
                client: default
                types:
                    user:
                        mappings:
                            # your mappings
                    doctrine:
                        driver: orm
                        model: Application\UserBundle\Entity\User
                        provider:

Two drivers are actually supported: orm and mongodb.

##### Use a custom doctrine query builder

You can control which entitie will be indexed by specifying a custom query builder method.

                    doctrine:
                        driver: orm
                        model: Application\UserBundle\Entity\User
                        provider:
                            query_builder_method: createIsActiveQueryBuilder

Your repository must implement this method and return a doctrine query builder.
                            
