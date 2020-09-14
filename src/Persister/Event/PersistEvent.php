<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;

interface PersistEvent
{
    /**
     * @return PagerInterface
     */
    public function getPager();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return ObjectPersisterInterface
     */
    public function getObjectPersister();
}
