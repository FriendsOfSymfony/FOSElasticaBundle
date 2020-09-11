Manual pager provider
====================

Create a service with the tag "fos_elastica.pager_provider" and attributes for the
index for which the service will provide.

```yaml
# app/config/config.yml
services:
    acme.search_provider.user:
        class: Acme\UserBundle\Provider\UserPagerProvider
        tags:
            - { name: fos_elastica.pager_provider, index: user }
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
        return new PagerfantaPager(new Pagerfanta(new ArrayAdapter([ /* an array of objects */ ])));
    }
}
```

There are some examples:

* [ORMPagerProvider](../../src/Doctrine/ORMPagerProvider.php)
* [PHPCRPagerProvider](../../src/Doctrine/PHPCRPagerProvider.php)
* [MongoDBPagerProvider](../../src/Doctrine/MongoDBPagerProvider.php)

[Back to index](../index.md)
