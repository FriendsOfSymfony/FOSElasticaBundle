<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

use FOS\ElasticaBundle\Event\AbstractIndexPopulateEvent;

/**
 * @phpstan-import-type TOptions from AbstractIndexPopulateEvent
 * @phpstan-type TPagerProviderOptions = TOptions|array<string, mixed>
 */
interface PagerProviderInterface
{
    /**
     * @phpstan-param TPagerProviderOptions $options
     */
    public function provide(array $options = []): PagerInterface;
}
