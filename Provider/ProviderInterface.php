<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
