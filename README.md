[Elastica](https://github.com/ruflin/Elastica) integration in Symfony2

## Installation

### Install elasticsearch

http://www.elasticsearch.org/guide/reference/setup/installation.html

### Install Elastica

#### Download

**With submodule**

 `git submodule add git://github.com/ruflin/Elastica vendor/elastica`

**With clone**

 `git clone git://github.com/ruflin/Elastica vendor/elastica`

**Using the vendors script**

Add the following lines to your deps file:

    [Elastica]
        git=git://github.com/ruflin/Elastica.git
        target=elastica

#### Register autoloading

    // app/autoload.php

    $loader->registerPrefixes(array(
        ...
        'Elastica' => __DIR__.'/../vendor/elastica/lib',
    ));

### Install ElasticaBundle

Use the master branch with Symfony2 master only, use the 2.0 branch with Symfony2.0.x releases.

#### Download

**With submodule**

 `git submodule add git://github.com/Exercise/FOQElasticaBundle vendor/bundles/FOQ/ElasticaBundle`

**With clone**

 `git clone git://github.com/Exercise/FOQElasticaBundle vendor/bundles/FOQ/ElasticaBundle`

**With the vendors script**

Add the following lines to your deps file:

    [FOQElasticaBundle]
        git=git://github.com/Exercise/FOQElasticaBundle.git
        target=bundles/FOQ/ElasticaBundle

For the 2.0 branch for use with Symfony2.0.x releases add the following:

    [FOQElasticaBundle]
        git=git://github.com/Exercise/FOQElasticaBundle.git
        target=bundles/FOQ/ElasticaBundle
        version=origin/2.0

Run the vendors script:

```bash
$ php bin/vendors install
```
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

Our index is now available as a service: `foq_elastica.index.website`. It is an instance of `Elastica_Index`.

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

Our type is now available as a service: `foq_elastica.index.website.user`. It is an instance of `Elastica_Type`.

### Populate the types

    php app/console foq:elastica:populate

This command deletes and creates the declared indexes and types.
It applies the configured mappings to the types.

This command needs providers to insert new documents in the elasticsearch types.
There are 2 ways to create providers.
If your elasticsearch type matches a doctrine repository, go for the doctrine automatic provider.
Or, for complete flexibility, go for manual provider.

#### Doctrine automatic provider

If we want to index the entities from a doctrine repository,
some configuration will let ElasticaBundle do it for us.

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
                            # more mappings...
                        doctrine:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            provider:

Two drivers are actually supported: orm and mongodb.

##### Use a custom doctrine query builder

You can control which entities will be indexed by specifying a custom query builder method.

                        doctrine:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            provider:
                                query_builder_method: createIsActiveQueryBuilder

Your repository must implement this method and return a doctrine query builder.

##### Change the batch size

By default, ElasticaBundle will index documents by paquets of 100.
You can change this value in the provider configuration.

                        doctrine:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            provider:
                                batch_size: 100

##### Change the document identifier field

By default, ElasticaBundle will use the `id` field of your entities as the elasticsearch document identifier.
You can change this value in the provider configuration.

                        doctrine:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            provider:
                                identifier: id

#### Manual provider

Create a service with the tag "foq_elastica.provider".

        <service id="acme.search_provider.user" class="Acme\UserBundle\Search\UserProvider">
            <tag name="foq_elastica.provider" />
            <argument type="service" id="foq_elastica.index.website.user" />
        </service>

Its class must implement `FOQ\ElasticaBundle\Provider\ProviderInterface`.

        <?php

        namespace Acme\UserBundle\Provider;

        use FOQ\ElasticaBundle\Provider\ProviderInterface;
        use Elastica_Type;

        class UserProvider implements ProviderInterface
        {
            protected $userType;

            public function __construct(Elastica_Type $userType)
            {
                $this->userType = $userType;
            }

            /**
             * Insert the repository objects in the type index
             *
             * @param Closure $loggerClosure
             */
            public function populate(Closure $loggerClosure)
            {
                $loggerClosure('Indexing users');

                $this->userType->addDocuments(array(
                    array('username' => 'Bob')
                ));
            }
        }

You will find a more complete implementation example in src/FOQ/ElasticaBundle/Provider/DoctrineProvider.php

### Search

You can just use the index and type Elastica objects, provided as services, to perform searches.

    /** var Elastica_Type */
    $userType = $this->container->get('foq_elastica.index.website.user');

    /** var Elastica_ResultSet */
    $resultSet = $userType->search('bob');

#### Doctrine finder

If your elasticsearch type is bound to a doctrine entity repository,
you can get your entities instead of Elastica results when you perform a search.
Declare that you want a doctrine finder in your configuration:

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
                            finder:

You can now use the `foq_elastica.finder.website.user` service:

    /** var FOQ\ElasticaBundle\Finder\MappedFinder */
    $finder = $container->get('foq_elastica.finder.website.user');

    /** var array of Acme\UserBundle\Entity\User */
    $users = $finder->find('bob');

    /** var array of Acme\UserBundle\Entity\User limited to 10 results */
    $users = $finder->find('bob', 10);

You can even get paginated results!

    /** var Pagerfanta\Pagerfanta */
    $userPaginator = $finder->findPaginated('bob');

### Realtime, selective index update

If you use the doctrine integration, you can let ElasticaBundle update the indexes automatically
when an object is added, updated or removed. It uses doctrine lifecycle events.
Declare that you want to update the index in real time:

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
                            listener: # by default, listens to "insert", "update" and "delete"

Now the index is automatically updated each time the state of the bound doctrine repository changes.
No need to repopulate the whole "user" index when a new `User` is created.

You can also choose to only listen for some of the events:

                        doctrine:
                            listener:
                                insert: true
                                update: false
                                delete: true

### Advanced elasticsearch configuration

Any setting can be specified when declaring a type. For example, to enable a custom analyzer, you could write:

    foq_elastica:
        indexes:
            doc:
                settings: 
                    index: 
                        analysis: 
                            analyzer: 
                                my_analyzer: 
                                    type: custom
                                    tokenizer: lowercase
                                    filter   : [my_ngram]
                            filter: 
                                my_ngram: 
                                    type: "nGram"
                                    min_gram: 3
                                    max_gram: 5
                types:
                    blog:
                        mappings:
                            title: { boost: 8, analyzer: my_analyzer }

### Overriding the Client class to suppress exceptions

By default, exceptions from the Elastica client library will propogate through
the bundle's Client class. For instance, if the elasticsearch server is offline,
issuing a request will result in an `Elastica_Exception_Client` being thrown.
Depending on your needs, it may be desirable to suppress these exceptions and
allow searches to fail silently.

One way to achieve this is to override the `foq_elastica.client.class` service
container parameter with a custom class. In the following example, we override
the `Client::request()` method and return the equivalent of an empty search
response if an exception occurred.

```
<?php

namespace Acme\ElasticaBundle;

use FOQ\ElasticaBundle\Client as BaseClient;

class Client extends BaseClient
{
    public function request($path, $method, $data = array())
    {
        try {
            return parent::request($path, $method, $data);
        } catch (\Elastica_Exception_Abstract $e) {
            return new \Elastica_Response('{"took":0,"timed_out":false,"hits":{"total":0,"max_score":0,"hits":[]}}');
        }
    }
}
```
