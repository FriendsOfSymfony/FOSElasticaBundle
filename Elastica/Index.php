<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Index as BaseIndex;

/**
 * Overridden Elastica Index class that provides dynamic index name changes.
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class Index extends BaseIndex
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
