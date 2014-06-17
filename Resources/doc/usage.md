FOSElasticaBundle Usage
=======================

Basic Searching with a Finder
-----------------------------

The most useful searching method is to use a finder defined by the type configuration.
A finder will return results that have been hydrated by the configured persistence backend,
allowing you to use relationships of returned entities. For more information about
configuration options for this kind of searching, please see the [types](types.md)
documentation.

```php
$finder = $this->container->get('fos_elastica.finder.search.user');

// Option 1. Returns all users who have example.net in any of their mapped fields
$results = $finder->find('example.net');

// Option 2. Returns a set of hybrid results that contain all Elasticsearch results
// and their transformed counterparts. Each result is an instance of a HybridResult
$results = $finder->findHybrid('example.net');

// Option 3a. Pagerfanta'd resultset
/** var Pagerfanta\Pagerfanta */
$userPaginator = $finder->findPaginated('bob');
$countOfResults = $userPaginator->getNbResults();

// Option 3b. KnpPaginator resultset

```

Faceted Searching
-----------------

When searching with facets, the facets can be retrieved when using the paginated
methods on the finder.

```php
$query = new \Elastica\Query();
$facet = new \Elastica\Facet\Terms('tags');
$facet->setField('companyGroup');
$query->addFacet($facet);

$companies = $finder->findPaginated($query);
$companies->setMaxPerPage($params['limit']);
$companies->setCurrentPage($params['page']);

$facets = $companies->getAdapter()->getFacets());
```

Searching the entire index
--------------------------

You can also define a finder that will work on the entire index. Adjust your index
configuration as per below:

```yaml
fos_elastica:
    indexes:
        website:
            finder: ~
```

You can now use the index wide finder service `fos_elastica.finder.website`:

```php
/** var FOS\ElasticaBundle\Finder\MappedFinder */
$finder = $this->container->get('fos_elastica.finder.website');

// Returns a mixed array of any objects mapped
$results = $finder->find('bob');
```

Type Repositories
-----------------

In the case where you need many different methods for different searching terms, it
may be better to separate methods for each type into their own dedicated repository
classes, just like Doctrine ORM's EntityRepository classes.

The manager class that handles repositories has a service key of `fos_elastica.manager`.
The manager will default to handling ORM entities, and the configuration must be changed
for MongoDB users.

```yaml
fos_elastica:
    default_manager: mongodb
```

An example for using a repository:

```php
/** var FOS\ElasticaBundle\Manager\RepositoryManager */
$repositoryManager = $this->container->get('fos_elastica.manager');

/** var FOS\ElasticaBundle\Repository */
$repository = $repositoryManager->getRepository('UserBundle:User');

/** var array of Acme\UserBundle\Entity\User */
$users = $repository->find('bob');
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

Assuming a type is configured as follows:

```yaml
fos_elastica:
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
                        provider: ~
                        finder: ~
```

The following code will execute a search against the Elasticsearch server:

```php
$finder = $this->container->get('fos_elastica.finder.site.article');
$boolQuery = new \Elastica\Query\Bool();

$fieldQuery = new \Elastica\Query\Match();
$fieldQuery->setFieldQuery('title', 'I am a title string');
$fieldQuery->setFieldParam('title', 'analyzer', 'my_analyzer');
$boolQuery->addShould($fieldQuery);

$tagsQuery = new \Elastica\Query\Terms();
$tagsQuery->setTerms('tags', array('tag1', 'tag2'));
$boolQuery->addShould($tagsQuery);

$categoryQuery = new \Elastica\Query\Terms();
$categoryQuery->setTerms('categoryIds', array('1', '2', '3'));
$boolQuery->addMust($categoryQuery);

$data = $finder->find($boolQuery);
```
