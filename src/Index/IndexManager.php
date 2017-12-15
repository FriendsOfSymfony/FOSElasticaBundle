<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Index;

use FOS\ElasticaBundle\Elastica\Index;

class IndexManager
{
    /**
     * @var Index
     */
    private $defaultIndex;

    /**
     * @var array
     */
    private $indexes;

    /**
     * @param array $indexes
     * @param Index $defaultIndex
     */
    public function __construct(array $indexes, Index $defaultIndex)
    {
        $this->defaultIndex = $defaultIndex;
        $this->indexes = $indexes;
    }

    /**
     * Gets all registered indexes.
     *
     * @return array
     */
    public function getAllIndexes()
    {
        return $this->indexes;
    }

    /**
     * Gets an index by its name.
     *
     * @param string $name Index to return, or the default index if null
     *
     * @return Index
     *
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function getIndex($name = null)
    {
        if (null === $name) {
            return $this->defaultIndex;
        }

        if (!isset($this->indexes[$name])) {
            throw new \InvalidArgumentException(sprintf('The index "%s" does not exist', $name));
        }

        return $this->indexes[$name];
    }

    /**
     * Gets the default index.
     *
     * @return Index
     */
    public function getDefaultIndex()
    {
        return $this->defaultIndex;
    }
}
