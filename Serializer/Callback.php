<?php

namespace FOS\ElasticaBundle\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class Callback
{
    protected $serializer;
    protected $groups = array();
    protected $version;
    protected $serializeNull;

    /**
     * @param $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
        if (!method_exists($this->serializer, 'serialize')) {
            throw new \RuntimeException('The serializer must have a "serialize" method.');
        }
    }

    /**
     * @param array $groups
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @param $version
     */
    public function setVersion($version)
    {
        $this->version = $version;

        if ($this->version && !$this->serializer instanceof SerializerInterface) {
            throw new \RuntimeException('Setting serialization version requires using "JMS\Serializer\Serializer".');
        }
    }

    /**
     * @param $serializeNull
     */
    public function setSerializeNull($serializeNull)
    {
        $this->serializeNull = $serializeNull;

        if (true === $this->serializeNull && !$this->serializer instanceof SerializerInterface) {
            throw new \RuntimeException('Setting null value serialization option requires using "JMS\Serializer\Serializer".');
        }
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    public function serialize($object)
    {
        if ($this->serializer instanceof SerializerInterface) {
            $context = SerializationContext::create()->enableMaxDepthChecks();

            if (!empty($this->groups)) {
                $context->setGroups($this->groups);
            }

            if ($this->version) {
                $context->setVersion($this->version);
            }

            $context->setSerializeNull($this->serializeNull);
        } else {
            $context = array();

            if (!empty($this->groups)) {
                $context['groups'] = $this->groups;
            }
        }

        return $this->serializer->serialize($object, 'json', $context);
    }
}
