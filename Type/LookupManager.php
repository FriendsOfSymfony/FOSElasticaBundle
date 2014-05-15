<?php

namespace FOS\ElasticaBundle\Type;

class LookupManager
{
    /**
     * @var LookupInterface[]
     */
    private $lookups = array();

    /**
     * @param LookupInterface[] $lookups
     */
    public function __construct($lookups)
    {
        foreach ($lookups as $lookup) {
            $this->lookups[$lookup->getKey()] = $lookup;
        }
    }

    /**
     * @param string $type
     * @return LookupInterface
     * @throws \InvalidArgumentException
     */
    public function getLookup($type)
    {
        if (!array_key_exists($type, $this->lookups)) {
            throw new \InvalidArgumentException(sprintf('Lookup with key "%s" does not exist', $type));
        }

        return $this->lookups[$type];
    }
}
