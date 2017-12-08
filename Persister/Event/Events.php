<?php
namespace FOS\ElasticaBundle\Persister\Event;

final class Events
{
    const PRE_PERSIST = 'elastica.pager_persister.pre_persist';

    const PRE_FETCH_OBJECTS = 'elastica.pager_persister.pre_fetch_objects';

    const PRE_INSERT_OBJECTS = 'elastica.pager_persister.pre_insert_objects';

    const POST_INSERT_OBJECTS = 'elastica.pager_persister.post_insert_objects';

    const ON_EXCEPTION = 'elastica.pager_persister.on_exception';

    const POST_PERSIST = 'elastica.pager_persister.post_persist';

    private function __construct()
    {
    }
}