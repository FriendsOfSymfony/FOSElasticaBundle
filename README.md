[Elastica](https://github.com/ruflin/Elastica) integration in Symfony2

### Installation

#### Bundle and Dependencies

For Symfony 2.0.x projects, you must use a 1.x release of this bundle. Please
check the bundle
[tags](https://github.com/FriendsOfSymfony/FOSElasticaBundle/tags) or the
[Packagist](https://packagist.org/packages/friendsofsymfony/elastica-bundle)
page for information on Symfony and Elastica compatibility.

Add FOSElasticaBundle to your application's `composer.json` file:

```json
{
    "require": {
        "friendsofsymfony/elastica-bundle": "~2.0"
    }
}
```

Install the bundle and its dependencies with the following command:

```bash
$ php composer.phar update friendsofsymfony/elastica-bundle
```

You may rely on Composer to fetch the appropriate version of Elastica. Lastly,
enable the bundle in your application kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\ElasticaBundle\FOSElasticaBundle(),
    );
}
```

#### Elasticsearch

Instructions for installing and deploying Elasticsearch may be found
[here](http://www.elasticsearch.org/guide/reference/setup/installation/).

### Basic configuration

#### Declare a client

Elasticsearch client is comparable to a database connection.
Most of the time, you will need only one.

    #app/config/config.yml
    fos_elastica:
        clients:
            default: { host: localhost, port: 9200 }

#### Declare an index

Elasticsearch index is comparable to Doctrine entity manager.
Most of the time, you will need only one.

    fos_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        indexes:
            website:
                client: default

Here we created a "website" index, that uses our "default" client.

Our index is now available as a service: `fos_elastica.index.website`. It is an instance of `Elastica_Index`.

If you need to have different index name from the service name, for example, 
in order to have different indexes for different environments then you can 
use the ```index_name``` key to change the index name. The service name will 
remain the same across the environments:

    fos_elastica:
        clients:
            default: { host: localhost, port: 9200 }
        indexes:
            website:
                client: default
                index_name: website_qa
                
The service id will be `fos_elastica.index.website` but the underlying index name is website_qa.           

#### Declare a type

Elasticsearch type is comparable to Doctrine entity repository.

    fos_elastica:
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
                            aboutMe: ~

Our type is now available as a service: `fos_elastica.index.website.user`. It is an instance of `Elastica_Type`.

### Declaring parent field

    fos_elastica:
        clients:
            default: { host: localhost, port: 9200 }
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

    fos_elastica:
        clients:
            default: { host: localhost, port: 9200 }
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

    php app/console fos:elastica:populate

This command deletes and creates the declared indexes and types.
It applies the configured mappings to the types.

This command needs providers to insert new documents in the elasticsearch types.
There are 2 ways to create providers.
If your elasticsearch type matches a Doctrine repository or a Propel query, go for the persistence automatic provider.
Or, for complete flexibility, go for manual provider.

#### Persistence automatic provider

If we want to index the entities from a Doctrine repository or a Propel query,
some configuration will let ElasticaBundle do it for us.

    fos_elastica:
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

Create a service with the tag "fos_elastica.provider" and attributes for the
index and type for which the service will provide.

        <service id="acme.search_provider.user" class="Acme\UserBundle\Search\UserProvider">
            <tag name="fos_elastica.provider" index="website" type="user" />
            <argument type="service" id="fos_elastica.index.website.user" />
        </service>

Its class must implement `FOS\ElasticaBundle\Provider\ProviderInterface`.

        <?php

        namespace Acme\UserBundle\Provider;

        use FOS\ElasticaBundle\Provider\ProviderInterface;
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
             * @param \Closure $loggerClosure
             * @param array    $options
             */
            public function populate(\Closure $loggerClosure = null, array $options = array())
            {
                if ($loggerClosure) {
                    $loggerClosure('Indexing users');
                }

                $document = new \Elastica_Document();
                $document->setData(array('username' => 'Bob'));
                $this->userType->addDocuments(array($document));
            }
        }

You will find a more complete implementation example in `src/FOS/ElasticaBundle/Doctrine/AbstractProvider.php`.

### Search

You can just use the index and type Elastica objects, provided as services, to perform searches.

    /** var Elastica_Type */
    $userType = $this->container->get('fos_elastica.index.website.user');

    /** var Elastica_ResultSet */
    $resultSet = $userType->search('bob');

#### Doctrine/Propel finder

If your elasticsearch type is bound to a Doctrine entity repository or a Propel query,
you can get your entities instead of Elastica results when you perform a search.
Declare that you want a Doctrine/Propel finder in your configuration:

    fos_elastica:
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

You can now use the `fos_elastica.finder.website.user` service:

    /** var FOS\ElasticaBundle\Finder\TransformedFinder */
    $finder = $container->get('fos_elastica.finder.website.user');

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

    /** var array of FOS\ElasticaBundle\HybridResult */
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

    fos_elastica:
        indexes:
            website:
                client: default
                finder: ~

You can now use the index wide finder service `fos_elastica.finder.website`:

    /** var FOS\ElasticaBundle\Finder\MappedFinder */
    $finder = $container->get('fos_elastica.finder.website');

    // Returns a mixed array of any objects mapped
    $results = $finder->find('bob');

#### Repositories

As well as using the finder service for a particular Doctrine/Propel entity you
can use a manager service for each driver and get a repository for an entity to search
against. This allows you to use the same service rather than the particular finder. For
example:

    /** var FOS\ElasticaBundle\Manager\RepositoryManager */
    $repositoryManager = $container->get('fos_elastica.manager.orm');

    /** var FOS\ElasticaBundle\Repository */
    $repository = $repositoryManager->getRepository('UserBundle:User');

    /** var array of Acme\UserBundle\Entity\User */
    $users = $repository->find('bob');

You can also specify the full name of the entity instead of the shortcut syntax:

    /** var FOS\ElasticaBundle\Repository */
    $repository = $repositoryManager->getRepository('Application\UserBundle\Entity\User');

> The **2.0**  branch doesn't support using `UserBundle:User` style syntax and you must use the full name of the entity. .

##### Default Manager

If you are only using one driver then its manager service is automatically aliased
to `fos_elastica.manager`. So the above example could be simplified to:

    /** var FOS\ElasticaBundle\Manager\RepositoryManager */
    $repositoryManager = $container->get('fos_elastica.manager');

    /** var FOS\ElasticaBundle\Repository */
    $repository = $repositoryManager->getRepository('UserBundle:User');

    /** var array of Acme\UserBundle\Entity\User */
    $users = $repository->find('bob');

If you use multiple drivers then you can choose which one is aliased to `fos_elastica.manager`
using the `default_manager` parameter:

    fos_elastica:
        default_manager: mongodb #defauults to orm
        clients:
            default: { host: localhost, port: 9200 }
        #--

##### Custom Repositories

As well as the default repository you can create a custom repository for an entity and add
methods for particular searches. These need to extend `FOS\ElasticaBundle\Repository` to have
access to the finder:

```
<?php

namespace Acme\ElasticaBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

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

    fos_elastica:
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

    /** var FOS\ElasticaBundle\Manager\RepositoryManager */
    $repositoryManager = $container->get('fos_elastica.manager');

    /** var FOS\ElasticaBundle\Repository */
    $repository = $repositoryManager->getRepository('UserBundle:User');

    /** var array of Acme\UserBundle\Entity\User */
    $users = $repository->findWithCustomQuery('bob');

Alternatively you can specify the custom repository using an annotation in the entity:

```
<?php

namespace Application\UserBundle\Entity;

use FOS\ElasticaBundle\Configuration\Search;

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

    fos_elastica:
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

### Ignoring missing index results

By default, FOSElasticaBundle will throw an exception if the results returned from
Elasticsearch are different from the results it finds from the chosen persistence
provider. This may pose problems for a large index where updates do not occur instantly
or another process has removed the results from your persistence provider without
updating Elasticsearch.

The error you're likely to see is something like:
'Cannot find corresponding Doctrine objects for all Elastica results.'

To solve this issue, each mapped object can be configured to ignore the missing results:

                        persistence:
                            elastica_to_model_transformer:
                                ignore_missing: true

### Advanced elasticsearch configuration

Any setting can be specified when declaring a type. For example, to enable a custom analyzer, you could write:

    fos_elastica:
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

One way to achieve this is to override the `fos_elastica.client.class` service
container parameter with a custom class. In the following example, we override
the `Client::request()` method and return the equivalent of an empty search
response if an exception occurred.

```
<?php

namespace Acme\ElasticaBundle;

use FOS\ElasticaBundle\Client as BaseClient;

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
$finder = $this->container->get('fos_elastica.finder.website.article');
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
fos_elastica:
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

#### Date format example

If you want to specify a [date format](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-date-format.html):

```yaml
fos_elastica:
    clients:
        default: { host: localhost, port: 9200 }
    indexes:
        site:
            types:
                user:
                    mappings:
                        username: { type: string }
                        lastlogin: { type: date, format: basic_date_time }
                        birthday: { type: date, format: "yyyy-MM-dd" }
```
