<?php

namespace FOQ\ElasticaBundle\Serializer;

class Callback
{
    protected $serializer;

    protected $groups;

    public function setSerializer($serializer){
        $this->serializer = $serializer;
    }

    public function setGroups($groups){
        $this->groups = $groups;
    }

    public function serialize($object)
    {
        $this->serializer->setGroups(null);
        $this->serializer->setGroups($this->groups);
        return $this->serializer->serialize($object, 'json');
    }
}
