Manual pager provider
====================

Create a service with the tag "fos_elastica.provider" and attributes for the
index and type for which the service will provide.

```yaml
# app/config/config.yml
services:
    acme.search_provider.user:
        class: Acme\UserBundle\Provider\UserPagerProvider
        tags:
            - { name: fos_elastica.pager_provider, index: app, type: user }
```

Its class must implement `FOS\ElasticaBundle\Provider\PagerProviderInterface`.

```php
<?php
namespace Acme\UserBundle\Provider;

use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

class UserPagerProvider implements PagerProviderInterface
{
    public function provide(array $options = array())
    {
        new PagerfantaPager(new Pagerfanta(new ArrayAdapter([ /* an array of objects */ ])));
    }
}
```

There are some examples:

* [DoctrineORMPagerProvider](https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/Doctrine/DoctrineORMPagerProvider.php)
* [DoctrinePHPCRPagerProvider](https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/Doctrine/DoctrinePHPCRPagerProvider.php)
* [DoctrineMongoDBPagerProvider](https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/Doctrine/DoctrineMongoDBPagerProvider.php)
* [Propel1PagerProvider](https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/Propel/Propel1PagerProvider.php)

[Back to index](../index.md)
