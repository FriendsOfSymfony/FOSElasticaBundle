<?php

namespace FOQ\ElasticaBundle\Provider;

/**
 * Insert application domain objects into elastica types
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface ProviderInterface
{
    /**
     * Persists all domain objects to ElasticSearch for this provider.
     *
     * @param Closure $loggerClosure
     */
    function populate(\Closure $loggerClosure = null);
}
