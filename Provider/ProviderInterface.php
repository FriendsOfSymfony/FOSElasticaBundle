<?php

namespace FOS\ElasticaBundle\Provider;

@trigger_error(sprintf('The %s class is deprecated since version 4.1 and will be removed in 5.0.', ProviderInterface::class), E_USER_DEPRECATED);

/**
 * @deprecated since 4.1 will be removed in 5.x. Use PagerProvider instead
 * 
 * Insert application domain objects into elastica types.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface ProviderInterface
{
    /**
     * Persists all domain objects to ElasticSearch for this provider.
     *
     * The closure can expect 2 or 3 arguments:
     *   * The step size
     *   * The total number of objects
     *   * A message to output in error conditions (not normally provided)
     *
     * @param \Closure $loggerClosure
     * @param array    $options
     *
     * @return
     */
    public function populate(\Closure $loggerClosure = null, array $options = array());
}
