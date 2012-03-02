<?php

namespace FOQ\ElasticaBundle;

/**
 * Deletes and recreates indexes
 **/
class Resetter
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
