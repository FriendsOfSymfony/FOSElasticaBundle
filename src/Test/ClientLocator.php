<?php declare(strict_types=1);

namespace FOS\ElasticaBundle\Test;

class ClientLocator
{
    private array $clients = [];

    public function __construct(array $clients)
    {
        $this->clients = $clients;
    }
}
