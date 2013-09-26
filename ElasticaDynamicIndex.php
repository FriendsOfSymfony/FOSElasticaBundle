<?php

namespace FOS\ElasticaBundle;

use Elastica\Index;

/**
 * Elastica index capable of reassigning name dynamically
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class ElasticaDynamicIndex extends Index
{
    /**
     * Reassign index name
     *
     * While it's technically a regular setter for name property, it's specifically named overrideName, but not setName
     * since it's used for a very specific case and normally should not be used
     *
     * @param string $name Index name
     *
     * @return void
     */
    public function overrideName($name)
    {
        $this->_name = $name;
    }
}
