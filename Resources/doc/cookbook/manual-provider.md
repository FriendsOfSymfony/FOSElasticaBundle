Manual provider
===============

Create a service with the tag "fos_elastica.provider" and attributes for the
index and type for which the service will provide.

```yaml
# app/config/config.yml
services:
    acme.search_provider.user:
        class: Acme\UserBundle\Search\UserProvider
        arguments:
            - @fos_elastica.index.website.user
        tags:
            - { name: fos_elastica.provider, index: website, type: user }
```

Its class must implement `FOS\ElasticaBundle\Provider\ProviderInterface`.

```php

namespace Acme\UserBundle\Provider;

use FOS\ElasticaBundle\Provider\ProviderInterface;
use Elastica\Type;
use Elastica\Document;

class UserProvider implements ProviderInterface
{
    protected $userType;

    public function __construct(Type $userType)
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

        $document = new Document();
        $document->setData(array('username' => 'Bob'));
        $this->userType->addDocuments(array($document));
    }
}
```

You will find a more complete implementation example in `src/FOS/ElasticaBundle/Doctrine/AbstractProvider.php`.
