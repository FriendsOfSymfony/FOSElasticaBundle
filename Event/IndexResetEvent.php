<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

/**
 * Index ResetEvent.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class IndexResetEvent extends IndexEvent
{
    const PRE_INDEX_RESET = 'elastica.index.pre_reset';
    const POST_INDEX_RESET = 'elastica.index.post_reset';

    /**
     * @var bool
     */
    private $force;

    /**
     * @var bool
     */
    private $populating;

    /**
     * @param string $index
     * @param bool   $populating
     * @param bool   $force
     */
    public function __construct($index, $populating, $force)
    {
        parent::__construct($index);

        $this->force = $force;
        $this->populating = $populating;
    }

    /**
     * @return boolean
     */
    public function isForce()
    {
        return $this->force;
    }

    /**
     * @return boolean
     */
    public function isPopulating()
    {
        return $this->populating;
    }

    /**
     * @param boolean $force
     */
    public function setForce($force)
    {
        $this->force = $force;
    }
}
