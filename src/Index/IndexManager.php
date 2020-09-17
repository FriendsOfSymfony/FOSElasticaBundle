<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
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

    public function __construct(array $indexes, Index $defaultIndex)
    {
        $this->defaultIndex = $defaultIndex;
        $this->indexes = $indexes;
    }

    /**
     * Gets all registered indexes.
     */
    public function getAllIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * Gets an index by its name or the default index.
     *
     * @throws \InvalidArgumentException if no index exists for the given name
     */
    public function getIndex(?string $name = null): Index
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
     */
    public function getDefaultIndex(): Index
    {
        return $this->defaultIndex;
    }
}
