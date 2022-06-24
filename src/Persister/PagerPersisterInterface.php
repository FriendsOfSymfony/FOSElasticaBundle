<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Event\AbstractIndexPopulateEvent;
use FOS\ElasticaBundle\Provider\PagerInterface;

/**
 * @phpstan-import-type TOptions from AbstractIndexPopulateEvent
 * @phpstan-type TPagerPersisterOptions = TOptions|array{}
 */
interface PagerPersisterInterface
{
    /**
     * @phpstan-param TPagerPersisterOptions $options
     *
     * @return void
     */
    public function insert(PagerInterface $pager, array $options = []);
}
