[Elastica](https://github.com/ruflin/Elastica) integration in Symfony2

## Installation

### Install elasticsearch

http://www.elasticsearch.org/guide/reference/setup/installation.html

### Install Elastica

#### Download

With submodule: `git submodule add git://github.com/ruflin/Elastica vendor/elastica`

With clone: `git clone git://github.com/ruflin/Elastica vendor/elastica`

#### Register autoloading

    // app/autoload.php

    $loader->registerPrefixes(array(
        ...
        'Elastica' => __DIR__.'/../vendor/elastica/lib',
    ));

### Install ElasticaBundle

#### Download

With submodule: `git submodule add git://github.com/Exercise/ElasticaBundle src/FOQ/ElasticaBundle`

With clone: `git clone git://github.com/Exercise/ElasticaBundle src/FOQ/ElasticaBundle`

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
                            # your mappings
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

    /** var Zend\Paginator\Paginator */
    $userPaginator = $finder->findPaginated('bob');
