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
        $this->find($query);
    }
}
```

To use the custom repository specify it in the mapping for the entity:

```yaml
fos_elastica:
    clients:
        default: { host: localhost, port: 9200 }
    indexes:
        app:
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

Alternatively you can specify the custom repository using an annotation in the entity:

```php
<?php

namespace Application\UserBundle\Entity;

use FOS\ElasticaBundle\Annotation\Search;

/**
 * @Search(repositoryClass="Acme\ElasticaBundle\SearchRepository\UserRepository")
 */
class User
{

   //---

}
```
