<?php

namespace FOQ\ElasticaBundle\Provider;

use Closure;

interface ProviderInterface
{
    function populate(Closure $loggerClosure);
}
