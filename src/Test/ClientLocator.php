<?php

declare(strict_types=1);

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Test;

class ClientLocator
{
    private array $clients; // @phpstan-ignore-line is never read, only written. Used only in tests to forbid container to remove clients.

    public function __construct(array $clients)
    {
        $this->clients = $clients;
    }
}
