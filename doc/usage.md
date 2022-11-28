FOSElasticaBundle Usage
=======================

Basic Searching with a Finder
-----------------------------

The most useful searching method is to use a finder defined by the index configuration.
A finder will return results that have been hydrated by the configured persistence backend,
allowing you to use relationships of returned entities. For more information about
configuration options for this kind of searching, please see the [indexes](indexes.md)
documentation.

> This example assumes you have defined an index `user` in your `config.yml`.

```yaml
# config/services.yaml
services:
    # ...

    App\Controller\UserController:
        tags: ['controller.service_arguments']
        public: true
        arguments:
            - '@fos_elastica.finder.user'
```

```php
namespace App\Controller;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;

class UserController
{
    private $finder;

    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    public function userAction()
    {
        // Option 1. Returns all users who have example.net in any of their mapped fields
        $results = $this->finder->find('example.net');

        // Option 2. Returns a set of hybrid results that contain all Elasticsearch results
        // and their transformed counterparts. Each result is an instance of a HybridResult
        $results = $this->finder->findHybrid('example.net');

        // Option 3a. Pagerfanta'd resultset
        /** var Pagerfanta\Pagerfanta */
        $userPaginator = $this->finder->findPaginated('bob');
        $countOfResults = $userPaginator->getNbResults();

        // Option 3b. KnpPaginator resultset
        $paginator = $this->get('knp_paginator');
        $results = $this->finder->createPaginatorAdapter('bob');
        $pagination = $paginator->paginate($results, $page, 10);

        // You can specify additional options as the fourth parameter of Knp Paginator
        // paginate method to nested_filter and nested_sort

        $options = [
            'sortNestedPath' => 'owner',
            'sortNestedFilter' => new Query\Term(['enabled' => ['value' => true]]),
        ];

        // sortNestedPath and sortNestedFilter also accepts a callable
        // which takes the current sort field to get the correct sort path/filter

        $pagination = $paginator->paginate($results, $page, 10, $options);
    }
}
```

When searching with a finder, parameters can be passed which influence the Elasticsearch query in general.

For example, the `search_type` parameter (see [the Elasticsearch documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-search-type.html))
can be set as follows:

```php
$results = $this->finder->findHybrid('example.net', null, ['search_type' => 'dfs_query_then_fetch']);
```

Aggregations
-----------------

When searching with aggregations, they can be retrieved when using the paginated
methods on the finder.

```php
$query = new \Elastica\Query();
$agg = new \Elastica\Aggregation\Terms('tags');
$agg->setField('companyGroup');
$query->addAggregation($agg);

$companies = $this->finder->findPaginated($query);
$companies->setMaxPerPage($params['limit']);
$companies->setCurrentPage($params['page']);

$aggs = $companies->getAdapter()->getAggregations();
```

Searching the entire index
--------------------------

You can also define a finder that will work on the entire index. Adjust your index
configuration as per below:

```yaml
fos_elastica:
    indexes:
        app:
            finder: ~
```

You can now use the index wide finder service `fos_elastica.finder.app`:

```php
/** var FOS\ElasticaBundle\Finder\MappedFinder */
$finder = $this->container->get('fos_elastica.finder.app');

// Returns a mixed array of any objects mapped
$results = $finder->find('bob');
```

Index Repositories
-----------------

In the case where you need many different methods for different searching terms, it
may be better to separate methods for each index into their own dedicated repository
classes, just like Doctrine ORM's EntityRepository classes.

The manager class that handles repositories has a service key of `fos_elastica.manager`.
The manager will default to handling ORM entities, and the configuration must be changed
for MongoDB users.

```yaml
fos_elastica:
    default_manager: mongodb
```

An example for using a repository:

```yaml
# config/services.yaml
services:
    # ...

    App\Controller\UserController:
        tags: ['controller.service_arguments']
        public: true
        arguments:
            - '@fos_elastica.manager'
```

```php
namespace App\Controller;

use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;

class UserController extends Controller
{
    /** @var RepositoryManagerInterface */
    private $repositoryManager;

    public function __construct(RepositoryManagerInterface $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;
    }

    public function userAction()
    {
        $repository = $this->repositoryManager->getRepository('UserBundle:User');

        /** var array of App\UserBundle\Entity\User */
        $users = $repository->find('bob');
    }
}
```

For more information about customising repositories, see the cookbook entry
[Custom Repositories](cookbook/custom-repositories.md).

Using a custom query builder method for transforming results
------------------------------------------------------------

When returning results from Elasticsearch to be transformed by the bundle, the default
`createQueryBuilder` method on each objects Repository class will be called. In many
circumstances this is not ideal and you'd prefer to use a different method to join in
any entity relations that are required on the page that will be displaying the results.

```yaml
fos_elastica:
    indexes:
        user:
            persistence:
                elastica_to_model_transformer:
                    query_builder_method: createSearchQueryBuilder
```

An example for using a custom query builder method:

```php
class UserRepository extends EntityRepository
{
    /**
     * Used by Elastica to transform results to model
     * 
     * @param string $entityAlias
     * @return  Doctrine\ORM\QueryBuilder
     */
    public function createSearchQueryBuilder($entityAlias)
    {
        $qb = $this->createQueryBuilder($entityAlias);

        $qb->select($entityAlias, 'g')
            ->innerJoin($entityAlias.'.groups', 'g');

        return $qb;
    }
}
```

Advanced Searching Example
--------------------------

If you would like to perform more advanced queries, here is one example using
the snowball stemming algorithm.

It searches for Article entities using `title`, `tags`, and `categoryIds`.
Results must match at least one specified `categoryIds`, and should match the
`title` or `tags` criteria. Additionally, we define a snowball analyzer to
apply to queries against the `title` field.

Assuming an index is configured as follows:

```yaml
fos_elastica:
    indexes:
        article:
            settings:
                index:
                    analysis:
                        analyzer:
                            my_analyzer:
                                type: snowball
                                language: English
            persistence:
                driver: orm
                model: Acme\DemoBundle\Entity\Article
                provider: ~
                finder: ~
            properties:
                title: { boost: 10, analyzer: my_analyzer }
                tags:
                categoryIds:
```

The following code will execute a search against the Elasticsearch server:

```php
$finder = $this->container->get('fos_elastica.finder.app.article');
$boolQuery = new \Elastica\Query\BoolQuery();

$fieldQuery = new \Elastica\Query\MatchQuery();
$fieldQuery->setFieldQuery('title', 'I am a title string');
$fieldQuery->setFieldParam('title', 'analyzer', 'my_analyzer');
$boolQuery->addShould($fieldQuery);

$tagsQuery = new \Elastica\Query\Terms('tags', ['tag1', 'tag2']);
$boolQuery->addShould($tagsQuery);

$categoryQuery = new \Elastica\Query\Terms('categoryIds', ['1', '2', '3']);
$boolQuery->addMust($categoryQuery);

$data = $finder->find($boolQuery);
```

Note: While passing an array of queries like this
```php
$boolQuery->addShould([$fieldQuery, $tagsQuery]);
```
works fine with Elasticsearch up to version 7.6, it _will_ result in an error like

>[bool] failed to parse field [should]

with version 7.7 and above. Rewriting your code as follows is a possible workaround:

```php
$boolQuery->addShould($fieldQuery);
$boolQuery->addShould($tagsQuery);
```
