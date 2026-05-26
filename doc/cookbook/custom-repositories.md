##### Custom Repositories

As well as the default repository you can create a custom repository for an entity and add
methods for particular searches. These need to extend `FOS\ElasticaBundle\Repository` to have
access to the finder:

```php
<?php

namespace Acme\ElasticaBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;

class UserRepository extends Repository
{
    public function findWithCustomQuery($searchText)
    {
        // build $query with Elastica objects
        return $this->find($query);
    }
}
```

To use the custom repository specify it in the mapping for the entity:

```yaml
fos_elastica:
    clients:
        default: { hosts: ['http://localhost:9200'] }
    indexes:
        user:
            client: default
            persistence:
                driver: orm
                model: Application\UserBundle\Entity\User
                provider: ~
                finder: ~
                repository: Acme\ElasticaBundle\SearchRepository\UserRepository
            properties:
                # your properties
```

Then the custom queries will be available when using the repository returned from the manager:

```php
/** var FOS\ElasticaBundle\Manager\RepositoryManager */
$repositoryManager = $container->get('fos_elastica.manager');

/** var FOS\ElasticaBundle\Repository */
$repository = $repositoryManager->getRepository('UserBundle:User');

/** var array of Acme\UserBundle\Entity\User */
$users = $repository->findWithCustomQuery('bob');
```

##### Injecting dependencies into custom repositories

Custom repositories are registered as autowired services in the dependency injection
container. This means you can inject any service into your repository constructor,
alongside the finder:

```php
<?php

namespace Acme\ElasticaBundle\SearchRepository;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Repository;
use Psr\Log\LoggerInterface;

class UserRepository extends Repository
{
    private LoggerInterface $logger;

    public function __construct(PaginatedFinderInterface $finder, LoggerInterface $logger)
    {
        parent::__construct($finder);

        $this->logger = $logger;
    }

    public function findWithCustomQuery($searchText)
    {
        $this->logger->info('Searching for users with query: {query}', ['query' => $searchText]);

        // build $query with Elastica objects
        return $this->find($query);
    }
}
```

The `$finder` argument is automatically provided by the bundle. Any additional
constructor arguments will be resolved by the container's autowiring.
