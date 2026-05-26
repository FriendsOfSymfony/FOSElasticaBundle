<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Message;

use FOS\ElasticaBundle\Event\AbstractIndexPopulateEvent;

/**
 * @phpstan-import-type TOptions from AbstractIndexPopulateEvent
 */
class AsyncPersistPage
{
    /**
     * @phpstan-param TOptions $options
     */
    public function __construct(
        private readonly int $page,
        /**
         * @phpstan-var TOptions
         */
        private readonly array $options
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @phpstan-return TOptions
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
