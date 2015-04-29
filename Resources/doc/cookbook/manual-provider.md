Custom provider
===============

Usually the index gets populated from the database using the `orm` or `propel`
provider. But sometimes you might want to index stuff not stored in the
database. For example you want to index users from another backend or files
from some storage or some remote resources.

First you need to remove the `provider` stuff from the `config.yml`:

```yaml
# app/config/config.yml
fos_elastica:
    indexes:
        app:
            types:
                user:
                    mappings:
                        id: ~
                        username: ~
```

Next create a service with the tag `fos_elastica.provider` and attributes for
the index and type for which the service will provide.

```yaml
# app/config/services.yml
services:
    acme.search_provider.user:
        class: Acme\UserBundle\Provider\UserProvider
        arguments:
            - @fos_elastica.index.website.user
        tags:
            - { name: fos_elastica.provider, index: app, type: user }
```

Its class must implement `FOS\ElasticaBundle\Provider\ProviderInterface`.

```php
namespace Acme\UserBundle\Provider;

use Elastica\Document;
use Elastica\Type;
use FOS\ElasticaBundle\Provider\ProviderInterface;

class UserProvider implements ProviderInterface
{
    private $userType;

    public function __construct(Type $userType)
    {
        $this->userType = $userType;
    }

    /**
     * Insert the objects in the user type
     *
     * @param \Closure $loggerClosure
     * @param array    $options
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        // some logic to load our users from somewhere

        $usersCount = count($users);
        foreach ($users as $user) {
            if ($loggerClosure) {
                // first argument is step size, second argument total count
                // will display a nice progress bar while populating
                $loggerClosure(1, $usersCount);
            }

            $document = new Document();
            $document->setData(array(
                'id' => $user['id'],
                'username' => $user['username'],
            ));
            $this->userType->addDocuments(array($document));
        }

    }
}
```

Finally run `app/console` to populate your search index:

```shell
$ app/console fos:elastica:populate
431/431 [============================] 100%
Populating app/user
Refreshing app
```

And you are done! Congratulations!

