<?php

namespace FOQ\ElasticaBundle;

use Closure;

interface ProviderInterface
{
    function populate(Closure $loggerClosure);
}
