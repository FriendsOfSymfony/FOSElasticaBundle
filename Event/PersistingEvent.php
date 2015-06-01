<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Maks Rafalko <maks.rafalko@gmail.com>
 */
class PersistingEvent extends Event
{
    const INSERT_OBJECTS = 'fos_elastica.insert_objects';
    const REPLACE_OBJECTS = 'fos_elastica.replace_objects';

    /**
     * @var mixed
     */
    private $objects;

    /**
     * @param array $objects
     */
    public function __construct(array $objects)
    {
        $this->objects = $objects;
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param array $objects
     */
    public function setObjects($objects)
    {
        $this->objects = $objects;
    }
}
