<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister\Event;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

@\trigger_error(\sprintf('The %s class is deprecated since version 6.3 and will be removed in 7.0.', PostAsyncInsertObjectsEvent::class), \E_USER_DEPRECATED);

/**
 * @deprecated since 6.3 will be removed in 7.0
 */
final class PostAsyncInsertObjectsEvent extends Event implements PersistEvent
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(private readonly PagerInterface $pager, private readonly ObjectPersisterInterface $objectPersister, private readonly int $objectsCount, private readonly ?string $errorMessage, private readonly array $options)
    {
    }

    public function getPager(): PagerInterface
    {
        return $this->pager;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getObjectPersister(): ObjectPersisterInterface
    {
        return $this->objectPersister;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getObjectsCount(): int
    {
        return $this->objectsCount;
    }
}
