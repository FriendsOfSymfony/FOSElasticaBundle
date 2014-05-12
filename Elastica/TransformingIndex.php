<?php

namespace FOS\ElasticaBundle\Elastica;

use Elastica\Client;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use FOS\ElasticaBundle\Transformer\CombinedResultTransformer;

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
     * Creates a TransformingSearch instance instead of the default Elastica Search
     *
     * @param string $query
     * @param int|array $options
     * @return TransformingSearch
     */
    public function createSearch($query = '', $options = null)
    {
        $search = new TransformingSearch($this->getClient());
        $search->addIndex($this);
        $search->setOptionsAndQuery($options, $query);

        return $search;
    }

    /**
     * Returns a type object for the current index with the given name
     *
     * @param  string $type Type name
     * @return TransformingType Type object
     */
    public function getType($type)
    {
        return new TransformingType($this, $type);
    }

    /**
     * Reassign index name
     *
     * While it's technically a regular setter for name property, it's specifically named overrideName, but not setName
     * since it's used for a very specific case and normally should not be used
     *
     * @param string $name Index name
     */
    public function overrideName($name)
    {
        $this->_name = $name;
    }
}
