<?php
declare(strict_types=1);

namespace FOS\ElasticaBundle\Message\Handler;

use FOS\ElasticaBundle\Message\AsyncPersistPage;
use FOS\ElasticaBundle\Persister\AsyncPagerPersister;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AsyncPersistPageHandler implements MessageHandlerInterface
{
    /**
     * var AsyncMessagePersister
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
