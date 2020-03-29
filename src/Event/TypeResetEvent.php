<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use FOS\ElasticaBundle\Event\FOSElasticaEvent as Event;

/**
 * Type ResetEvent.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class TypeResetEvent extends IndexEvent
{
    /**
     * @Event("FOS\ElasticaBundle\Event\TypeResetEvent")
     */
    const PRE_TYPE_RESET = 'elastica.index.type_pre_reset';

    /**
     * @Event("FOS\ElasticaBundle\Event\TypeResetEvent")
     */
    const POST_TYPE_RESET = 'elastica.index.type_post_reset';

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $index
     * @param string $type
     */
    public function __construct($index, $type)
    {
        parent::__construct($index);

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
