<?php

namespace FOQ\ElasticaBundle;

use Elastica_Exception_Response;

/**
 * Deletes and recreates indexes
 **/
class Reseter
{
	/**
	 * Index settings and mappings
	 *
	 * @var array
	 */
	protected $indexConfigs;

    public function __construct(array $indexConfigs)
    {
		$this->indexConfigs = $indexConfigs;
    }

    /**
     * Resets all indexes
     *
     * @return null
     **/
    public function reset()
    {
        foreach ($this->indexConfigs as $indexConfig) {
            $indexConfig['index']->create($indexConfig['config'], true);
        }
    }
}
