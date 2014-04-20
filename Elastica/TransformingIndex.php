<?php

namespace FOS\ElasticaBundle\Index;

use Elastica\Index;

/**
 * Overridden Elastica Index class that provides dynamic index name changes
 * and returns our own ResultSet instead of the Elastica ResultSet.
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 * @author Tim Nagel <tim@nagel.com.au>
 */
class TransformingIndex extends Index
{
    /**
     * Indexes a
     * @param  string $query
     * @param  int|array $options
     * @return \Elastica\Search
     */
    public function createSearch($query = '', $options = null)
    {
        $search = new Search($this->getClient());
        $search->addIndex($this);
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

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
