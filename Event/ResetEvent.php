<?php

namespace FOS\ElasticaBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * ResetEvent
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class ResetEvent extends Event
{
    const PRE_INDEX_RESET = 'elastica.index.pre_reset';
    const POST_INDEX_RESET = 'elastica.index.post_reset';

    const PRE_TYPE_RESET = 'elastica.index.type_pre_reset';
    const POST_TYPE_RESET = 'elastica.index.type_post_reset';

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var string
     */
    private $indexType;

    /**
     * @var bool
     */
    private $populating;

    /**
     * @var bool
     */
    private $force;

    /**
     * @param string $indexName
     * @param string $indexType
     * @param bool   $populating
     * @param bool   $force
     */
    public function __construct($indexName, $indexType, $populating = false, $force = false)
    {
        $this->indexName  = $indexName;
        $this->indexType  = $indexType;
        $this->populating = (bool)$populating;
        $this->force      = (bool)$force;
    }

    /**
     * @return string
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * @return string
     */
    public function getIndexType()
    {
        return $this->indexType;
    }

    /**
     * @return boolean
     */
    public function isPopulating()
    {
        return $this->populating;
    }

    /**
     * @return boolean
     */
    public function isForce()
    {
        return $this->force;
    }
}