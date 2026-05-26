<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Message\Handler;

use FOS\ElasticaBundle\Message\AsyncPersistPage;
use FOS\ElasticaBundle\Persister\AsyncPagerPersister;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AsyncPersistPageHandler
{
    public function __construct(private readonly AsyncPagerPersister $persister)
    {
    }

    public function __invoke(AsyncPersistPage $message): void
    {
        $this->persister->insertPage($message->getPage(), $message->getOptions());
    }
}
