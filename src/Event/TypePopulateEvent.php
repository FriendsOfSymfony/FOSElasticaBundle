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

use Symfony\Component\EventDispatcher\Event as LegacyEvent;
use Symfony\Contracts\EventDispatcher\Event;

if (!class_exists(Event::class)) {
    /**
     * Symfony 3.4
     */

    /**
     * Type Populate Event.
     *
     * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
     */
    class TypePopulateEvent extends IndexPopulateEvent
    {
        /**
         * @LegacyEvent("FOS\ElasticaBundle\Event\TypePopulateEvent")
         */
        const PRE_TYPE_POPULATE = 'elastica.index.type_pre_populate';
        /**
         * @LegacyEvent("FOS\ElasticaBundle\Event\TypePopulateEvent")
         */
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
} else {
    /**
     * Symfony >= 4.3
     */

    /**
     * Type Populate Event.
     *
     * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
     */
    class TypePopulateEvent extends IndexPopulateEvent
    {
        /**
         * @LegacyEvent("FOS\ElasticaBundle\Event\TypePopulateEvent")
         */
        const PRE_TYPE_POPULATE = 'elastica.index.type_pre_populate';

        /**
         * @LegacyEvent("FOS\ElasticaBundle\Event\TypePopulateEvent")
         */
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
}
