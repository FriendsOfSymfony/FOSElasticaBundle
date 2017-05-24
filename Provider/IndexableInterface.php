<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

interface IndexableInterface
{
    /**
     * Checks if an object passed should be indexable or not.
     *
     * @param string $indexName
     * @param string $typeName
     * @param mixed  $object
     *
     * @return bool
     */
    public function isObjectIndexable($indexName, $typeName, $object);
}
