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
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AsyncPersistPageHandler implements MessageHandlerInterface
{
    /**
     * @var AsyncPagerPersister
     */
    private $persister;

    public function __construct(AsyncPagerPersister $persister)
    {
        $this->persister = $persister;
    }

    public function __invoke(AsyncPersistPage $message): void
    {
        $this->persister->insertPage($message->getPage(), $message->getOptions());
    }
}
