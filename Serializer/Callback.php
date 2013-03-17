<?php

namespace FOQ\ElasticaBundle\Serializer;

use JMS\Serializer\Serializer;

class Callback
{
    protected $serializer;

    protected $groups;

    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    public function serialize($object)
    {
        if ($this->serializer instanceof Serializer) {
            $this->serializer->setGroups($this->groups);
        } elseif ($this->groups) {
            throw new \RuntimeException('Setting serialization groups requires using "JMS\Serializer\Serializer"');
        }

        return $this->serializer->serialize($object, 'json');
    }
}
