<?php

namespace FOQ\ElasticaBundle;

class Populator
{
    protected $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function populate()
    {
        foreach ($this->providers as $provider) {
            $provider->populate();
        }
    }
}
