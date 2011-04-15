<?php

namespace FOQ\ElasticaBundle;

use Closure;

class Populator
{
    protected $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function populate(Closure $loggerClosure)
    {
        foreach ($this->providers as $name => $provider) {
            $provider->populate(function($text) use ($name, $loggerClosure) {
                $loggerClosure(sprintf('Indexing %s, %s', $name, $text));
            });
        }
    }
}
