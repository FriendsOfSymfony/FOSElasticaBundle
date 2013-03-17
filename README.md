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
        'FOQ' => __DIR__.'/../vendor/bundles',
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

Elasticsearch client is comparable to a database connection.
Most of the time, you will need only one.

    #app/config/config.yml
    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }


#### Declare a serializer

Elastica can handle objects instead of data arrays if a serializer callable is configured

    #app/config/config.yml
    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        serializer:
            callback: callback
            serializer: serializer

"callback" is the name of a parameter defining a class having a public method serialize($object). "serializer" is the service id for the
actual serializer, e.g. 'serializer' if you're using the JMSSerializerBundle. If this is configured you can use
Elastica_Type::addObject instead of Elastica_Type::addDocument to add data to the index.
The bundle provides a default implementation with a serializer service id 'serializer' that can be turned on by adding
the following line to your config.

    #app/config/config.yml
    foq_elastica:
        serializer: ~

#### Declare an index

Elasticsearch index is comparable to Doctrine entity manager.
Most of the time, you will need only one.

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        serializer:
            callable_class: %classname%
            id: serializer
        indexes:
            website:
                client: default

Here we created a "website" index, that uses our "default" client.

Our index is now available as a service: `foq_elastica.index.website`. It is an instance of `Elastica_Index`.

If you need to have different index name from the service name, for example, 
in order to have different indexes for different environments then you can 
use the ```index_name``` key to change the index name. The service name will 
remain the same across the environments:

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        indexes:
            website:
                client: default
                index_name: website_qa
                
The service id will be `foq_elastica.index.website` but the underlying index name is website_qa.           

#### Declare a type

Elasticsearch type is comparable to Doctrine entity repository.

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        serializer:
            callable_class: %classname%
            id: serializer
        indexes:
            website:
                client: default
                types:
                    user:
                        mappings:
                            username: { boost: 5 }
                            firstName: { boost: 3 }
                            lastName: { boost: 3 }
                            aboutMe: ~

Our type is now available as a service: `foq_elastica.index.website.user`. It is an instance of `Elastica_Type`.

### Declaring serializer groups

If you are using the JMSSerializerBundle for serializing objects passed to elastica you can define serializer groups
per type.

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        serializer:
            callable_class: %classname%
            id: serializer
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
                        serializer:
                            groups: [elastica, Default]

### Declaring parent field

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        serializer:
            callable_class: %classname%
            id: serializer
        indexes:
            website:
                client: default
                types:
                    comment:
                        mappings:
                            post: {_parent: { type: "post", identifier: "id" } }
                            date: { boost: 5 }
                            content: ~

### Declaring `nested` or `object`

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        serializer:
            callable_class: %classname%
            id: serializer
        indexes:
            website:
                client: default
                types:
                    post:
                        mappings:
                            date: { boost: 5 }
                            title: { boost: 3 }
                            content: ~
                            comments:
                                type: "nested"
                                properties:
                                    date: { boost: 5 }
                                    content: ~

### Populate the types

    php app/console foq:elastica:populate

This command deletes and creates the declared indexes and types.
It applies the configured mappings to the types.

This command needs providers to insert new documents in the elasticsearch types.
There are 2 ways to create providers.
If your elasticsearch type matches a Doctrine repository or a Propel query, go for the persistence automatic provider.
Or, for complete flexibility, go for manual provider.

#### Persistence automatic provider

If we want to index the entities from a Doctrine repository or a Propel query,
some configuration will let ElasticaBundle do it for us.

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        serializer:
            callable_class: %classname%
            id: serializer
        indexes:
            website:
                client: default
                types:
                    user:
                        mappings:
                            username: { boost: 5 }
                            firstName: { boost: 3 }
                            # more mappings...
                        persistence:
                            driver: orm # orm, mongodb, propel are available
                            model: Application\UserBundle\Entity\User
                            provider: ~

Three drivers are actually supported: orm, mongodb, and propel.

##### Use a custom Doctrine query builder

You can control which entities will be indexed by specifying a custom query builder method.

                        persistence:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            provider:
                                query_builder_method: createIsActiveQueryBuilder

Your repository must implement this method and return a Doctrine query builder.

> **Propel** doesn't support this feature yet.

##### Change the batch size

By default, ElasticaBundle will index documents by packets of 100.
You can change this value in the provider configuration.

                        persistence:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            provider:
                                batch_size: 100

##### Change the document identifier field

By default, ElasticaBundle will use the `id` field of your entities as the elasticsearch document identifier.
You can change this value in the persistence configuration.

                        persistence:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            identifier: id

#### Manual provider

Create a service with the tag "foq_elastica.provider" and attributes for the
index and type for which the service will provide.

        <service id="acme.search_provider.user" class="Acme\UserBundle\Search\UserProvider">
            <tag name="foq_elastica.provider" index="website" type="user" />
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
            public function populate(Closure $loggerClosure = null)
            {
                if ($loggerClosure) {
                    $loggerClosure('Indexing users');
                }

                $document = new \Elastica_Document();
                $document->setData(array('username' => 'Bob'));
                $this->userType->addDocuments(array($document));
            }
        }

You will find a more complete implementation example in `src/FOQ/ElasticaBundle/Doctrine/AbstractProvider.php`.

### Search

You can just use the index and type Elastica objects, provided as services, to perform searches.

    /** var Elastica_Type */
    $userType = $this->container->get('foq_elastica.index.website.user');

    /** var Elastica_ResultSet */
    $resultSet = $userType->search('bob');

#### Doctrine/Propel finder

If your elasticsearch type is bound to a Doctrine entity repository or a Propel query,
you can get your entities instead of Elastica results when you perform a search.
Declare that you want a Doctrine/Propel finder in your configuration:

    foq_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        serializer:
            callable_class: %classname%
            id: serializer
        indexes:
            website:
                client: default
                types:
                    user:
                        mappings:
                            # your mappings
                        persistence:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            provider: ~
                            finder: ~

You can now use the `foq_elastica.finder.website.user` service:

    /** var FOQ\ElasticaBundle\Finder\TransformedFinder */
    $finder = $container->get('foq_elastica.finder.website.user');

    /** var array of Acme\UserBundle\Entity\User */
    $users = $finder->find('bob');

    /** var array of Acme\UserBundle\Entity\User limited to 10 results */
    $users = $finder->find('bob', 10);

You can even get paginated results!

Pagerfanta:

    /** var Pagerfanta\Pagerfanta */
    $userPaginator = $finder->findPaginated('bob');

Knp paginator:

    $paginator = $this->get('knp_paginator');
    $userPaginator = $paginator->paginate($finder->createPaginatorAdapter('bob'));

You can also get both the Elastica results and the entities together from the finder.
You can then access the score, highlights etc. from the Elastica_Result whilst
still also getting the entity.

    /** var array of FOQ\ElasticaBundle\HybridResult */
    $hybridResults = $finder->findHybrid('bob');
    foreach ($hybridResults as $hybridResult) {

        /** var  Acme\UserBundle\Entity\User */
        $user = $hybridResult->getTransformed();

        /** var  Elastica_Result */
        $result = $hybridResult->getResult();
    }

##### Index wide finder

You can also define a finder that will work on the entire index. Adjust your index
configuration as per below:

    foq_elastica:
        indexes:
            website:
                client: default
                finder: ~

You can now use the index wide finder service `foq_elastica.finder.website`:

    /** var FOQ\ElasticaBundle\Finder\MappedFinder */
    $finder = $container->get('foq_elastica.finder.website');

    // Returns a mixed array of any objects mapped
    $results = $finder->find('bob');

#### Repositories

As well as using the finder service for a particular Doctrine/Propel entity you
can use a manager service for each driver and get a repository for an entity to search
against. This allows you to use the same service rather than the particular finder. For
example:

    /** var FOQ\ElasticaBundle\Manager\RepositoryManager */
    $repositoryManager = $container->get('foq_elastica.manager.orm');

    /** var FOQ\ElasticaBundle\Repository */
    $repository = $repositoryManager->getRepository('UserBundle:User');

    /** var array of Acme\UserBundle\Entity\User */
    $users = $repository->find('bob');

You can also specify the full name of the entity instead of the shortcut syntax:

    /** var FOQ\ElasticaBundle\Repository */
    $repository = $repositoryManager->getRepository('Application\UserBundle\Entity\User');

> The **2.0**  branch doesn't support using `UserBundle:User` style syntax and you must use the full name of the entity. .

##### Default Manager

If you are only using one driver then its manager service is automatically aliased
to `foq_elastica.manager`. So the above example could be simplified to:

    /** var FOQ\ElasticaBundle\Manager\RepositoryManager */
    $repositoryManager = $container->get('foq_elastica.manager');

    /** var FOQ\ElasticaBundle\Repository */
    $repository = $repositoryManager->getRepository('UserBundle:User');

    /** var array of Acme\UserBundle\Entity\User */
    $users = $repository->find('bob');

If you use multiple drivers then you can choose which one is aliased to `foq_elastica.manager`
using the `default_manager` parameter:

    foq_elastica:
        default_manager: mongodb #defauults to orm
        clients:
            default: { host: localhost, port: 9200 }
        #--

##### Custom Repositories

As well as the default repository you can create a custom repository for an entity and add
methods for particular searches. These need to extend `FOQ\ElasticaBundle\Repository` to have
access to the finder:

```
<?php

namespace Acme\ElasticaBundle\SearchRepository;

use FOQ\ElasticaBundle\Repository;

class UserRepository extends Repository
{
    public function findWithCustomQuery($searchText)
    {
        // build $query with Elastica objects
        $this->find($query);
    }
}
```

To use the custom repository specify it in the mapping for the entity:

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
                        persistence:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            provider: ~
                            finder: ~
                            repository: Acme\ElasticaBundle\SearchRepository\UserRepository

Then the custom queries will be available when using the repository returned from the manager:

    /** var FOQ\ElasticaBundle\Manager\RepositoryManager */
    $repositoryManager = $container->get('foq_elastica.manager');

    /** var FOQ\ElasticaBundle\Repository */
    $repository = $repositoryManager->getRepository('UserBundle:User');

    /** var array of Acme\UserBundle\Entity\User */
    $users = $repository->findWithCustomQuery('bob');

Alternatively you can specify the custom repository using an annotation in the entity:

```
<?php

namespace Application\UserBundle\Entity;

use FOQ\ElasticaBundle\Configuration\Search;

/**
 * @Search(repositoryClass="Acme\ElasticaBundle\SearchRepository\UserRepository")
 */
class User
{

   //---

}
```

### Realtime, selective index update

If you use the Doctrine integration, you can let ElasticaBundle update the indexes automatically
when an object is added, updated or removed. It uses Doctrine lifecycle events.
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
                        persistence:
                            driver: orm
                            model: Application\UserBundle\Entity\User
                            listener: # by default, listens to "insert", "update" and "delete"

Now the index is automatically updated each time the state of the bound Doctrine repository changes.
No need to repopulate the whole "user" index when a new `User` is created.

You can also choose to only listen for some of the events:

                        persistence:
                            listener:
                                insert: true
                                update: false
                                delete: true

> **Propel** doesn't support this feature yet.

### Checking an entity method for listener

If you use listeners to update your index, you may need to validate your
entities before you index them (e.g. only index "public" entities). Typically,
you'll want the listener to be consistent with the provider's query criteria.
This may be achieved by using the `is_indexable_callback` config parameter:

                        persistence:
                            listener:
                                is_indexable_callback: "isPublic"

If `is_indexable_callback` is a string and the entity has a method with the
specified name, the listener will only index entities for which the method
returns `true`. Additionally, you may provide a service and method name pair:

                        persistence:
                            listener:
                                is_indexable_callback: [ "%custom_service_id%", "isIndexable" ]

In this case, the callback will be the `isIndexable()` method on the specified
service and the object being considered for indexing will be passed as the only
argument. This allows you to do more complex validation (e.g. ACL checks).

As you might expect, new entities will only be indexed if the callback returns
`true`. Additionally, modified entities will be updated or removed from the
index depending on whether the callback returns `true` or `false`, respectively.
The delete listener disregards the callback.

> **Propel** doesn't support this feature yet.

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

By default, exceptions from the Elastica client library will propagate through
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

### Example of Advanced Query

If you would like to perform more advanced queries, here is one example using
the snowball stemming algorithm.

It searches for Article entities using `title`, `tags`, and `categoryIds`.
Results must match at least one specified `categoryIds`, and should match the
`title` or `tags` criteria. Additionally, we define a snowball analyzer to
apply to queries against the `title` field.

```php
$finder = $this->container->get('foq_elastica.finder.website.article');
$boolQuery = new \Elastica_Query_Bool();

$fieldQuery = new \Elastica_Query_Text();
$fieldQuery->setFieldQuery('title', 'I am a title string');
$fieldQuery->setFieldParam('title', 'analyzer', 'my_analyzer');
$boolQuery->addShould($fieldQuery);

$tagsQuery = new \Elastica_Query_Terms();
$tagsQuery->setTerms('tags', array('tag1', 'tag2'));
$boolQuery->addShould($tagsQuery);

$categoryQuery = new \Elastica_Query_Terms();
$categoryQuery->setTerms('categoryIds', array('1', '2', '3'));
$boolQuery->addMust($categoryQuery);

$data = $finder->find($boolQuery);
```

Configuration:

```yaml
foq_elastica:
    clients:
        default: { host: localhost, port: 9200 }
    indexes:
        site:
            settings:
                index:
                  analysis:
                        analyzer:
                            my_analyzer:
                                type: snowball
                                language: English
            types:
                article:
                    mappings:
                        title: { boost: 10, analyzer: my_analyzer }
                        tags:
                        categoryIds:
                    persistence:
                        driver: orm
                        model: Acme\DemoBundle\Entity\Article
                        provider:
                        finder:
```
