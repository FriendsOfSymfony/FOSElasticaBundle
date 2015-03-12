<?php

namespace FOS\ElasticaBundle\Provider;

/**
 * Insert application domain objects into elastica types.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface ProviderInterface
{
    /**
     * Persists all domain objects to ElasticSearch for this provider.
     *
     * @param \Closure $loggerClosure
     * @param array    $options
     *
     * @return
     */
    public function populate(\Closure $loggerClosure = null, array $options = array());
}
