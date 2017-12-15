<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Index as BaseIndex;

/**
 * Overridden Elastica Index class that provides dynamic index name changes.
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class Index extends BaseIndex
{
    private $originalName;

    /**
     * Stores created types to avoid recreation.
     *
     * @var array
     */
    private $typeCache = [];

    /**
     * Returns the original name of the index if the index has been renamed for reindexing
     * or realiasing purposes.
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName ?: $this->_name;
    }

    public function getType($type)
    {
        if (isset($this->typeCache[$type])) {
            return $this->typeCache[$type];
        }

        return $this->typeCache[$type] = parent::getType($type);
    }

    /**
     * Reassign index name.
     *
     * While it's technically a regular setter for name property, it's specifically named overrideName, but not setName
     * since it's used for a very specific case and normally should not be used
     *
     * @param string $name Index name
     */
    public function overrideName($name)
    {
        $this->originalName = $this->_name;
        $this->_name = $name;
    }
}
