<?php

namespace FOQ\ElasticaBundle\Provider;

use Closure;

/**
 * Insert application domain objects into elastica types
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface ProviderInterface
{
    /**
     * Add all domain objects of a repository to the elastica type
     *
     * @param Closure $loggerClosure
     */
    function populate(Closure $loggerClosure);
}
