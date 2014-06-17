<?php

namespace FOS\ElasticaBundle;

use Elastica\Index;

/**
 * Elastica index capable of reassigning name dynamically
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class DynamicIndex extends Index
{
    private $originalName;

    /**
     * Reassign index name
     *
     * While it's technically a regular setter for name property, it's specifically named
     * overrideName, but not setName since it's used for a very specific case and normally
     * should not be used.
     *
     * @param string $name Index name
     */
    public function overrideName($name)
    {
        $this->originalName = $this->_name;
        $this->_name = $name;
    }

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
}
