<?php

namespace FOS\ElasticaBundle\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class Callback
{
    protected $serializer;
    protected $groups;
    protected $version;

    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
        if (!method_exists($this->serializer, 'serialize')) {
            throw new \RuntimeException('The serializer must have a "serialize" method.');
        }
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;

        if ($this->groups) {
            if (!$this->serializer instanceof SerializerInterface) {
                throw new \RuntimeException('Setting serialization groups requires using "JMS\Serializer\Serializer".');
            }
        }
    }

    public function setVersion($version)
    {
        $this->version = $version;

        if ($this->version) {
            if (!$this->serializer instanceof SerializerInterface) {
                throw new \RuntimeException('Setting serialization version requires using "JMS\Serializer\Serializer".');
            }
        }
    }

    public function serialize($object)
    {
        $context = $this->serializer instanceof SerializerInterface ? SerializationContext::create()->enableMaxDepthChecks() : array();

        if ($this->groups) {
            $context->setGroups($this->groups);
        }

        if ($this->version) {
            $context->setVersion($this->version);
        }

        return $this->serializer->serialize($object, 'json', $context);
    }
}
