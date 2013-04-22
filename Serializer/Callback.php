<?php

namespace FOS\ElasticaBundle\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;

class Callback
{
    protected $serializer;

    protected $groups;

    protected $version;

    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function serialize($object)
    {
        $context = $this->serializer instanceof Serializer ? new SerializationContext() : null;

        if ($this->groups) {
            if (!$context) {
                throw new \RuntimeException('Setting serialization groups requires using "JMS\Serializer\Serializer"');
            }

            $context->setGroups($this->groups);
        }

        if ($this->version) {
            if (!$context) {
                throw new \RuntimeException('Setting serialization version requires using "JMS\Serializer\Serializer"');
            }

            $context->setVersion($this->version);
        }

        return $this->serializer->serialize($object, 'json', $context);
    }
}
