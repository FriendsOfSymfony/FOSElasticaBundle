<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) FriendsOfSymfony <https://github.com/FriendsOfSymfony/FOSElasticaBundle/graphs/contributors>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Type Populate Event.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class TypePopulateEvent extends IndexPopulateEvent
{
    const PRE_TYPE_POPULATE = 'elastica.index.type_pre_populate';
    const POST_TYPE_POPULATE = 'elastica.index.type_post_populate';

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $index
     * @param string $type
     * @param bool   $reset
     * @param array  $options
     */
    public function __construct($index, $type, $reset, $options)
    {
        parent::__construct($index, $reset, $options);

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
