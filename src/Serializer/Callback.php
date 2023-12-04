<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as JMSSerializer;
use Symfony\Component\Serializer\SerializerInterface;

class Callback
{
    /**
     * @var ?object
     */
    protected $serializer;
    /**
     * @var array<mixed>
     */
    protected $groups = [];
    /**
     * @var string
     */
    protected $version;
    /**
     * @var bool
     */
    protected $serializeNull = false;

    public function setSerializer(object $serializer): self
    {
        $this->serializer = $serializer;

        if (!\method_exists($this->serializer, 'serialize')) {
            throw new \RuntimeException('The serializer must have a "serialize" method.');
        }

        return $this;
    }

    /**
     * @param array<mixed> $groups
     */
    public function setGroups(array $groups): self
    {
        $this->groups = $groups;

        if (!empty($this->groups) && !$this->serializer instanceof SerializerInterface && !$this->serializer instanceof JMSSerializer) {
            throw new \RuntimeException(\sprintf('Setting serialization groups requires using a "%s" or "%s" serializer instance.', SerializerInterface::class, JMSSerializer::class));
        }

        return $this;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        if ($this->version && !$this->serializer instanceof JMSSerializer) {
            throw new \RuntimeException(\sprintf('Setting serialization version requires using a "%s" serializer instance.', JMSSerializer::class));
        }

        return $this;
    }

    public function setSerializeNull(bool $serializeNull): self
    {
        $this->serializeNull = $serializeNull;

        if (true === $this->serializeNull && !$this->serializer instanceof SerializerInterface && !$this->serializer instanceof JMSSerializer) {
            throw new \RuntimeException(\sprintf('Setting null value serialization option requires using a "%s" or "%s" serializer instance.', SerializerInterface::class, JMSSerializer::class));
        }

        return $this;
    }

    public function serialize($object): string
    {
        $context = $this->serializer instanceof JMSSerializer ? SerializationContext::create()->enableMaxDepthChecks() : [];

        if (!empty($this->groups)) {
            if ($context instanceof SerializationContext) {
                $context->setGroups($this->groups);
            } else {
                $context['groups'] = $this->groups;
            }
        }

        if ($this->version) {
            $context->setVersion($this->version);
        }

        if ($context instanceof SerializationContext) {
            $context->setSerializeNull($this->serializeNull);
        } else {
            $context['skip_null_values'] = !$this->serializeNull;
        }

        return $this->serializer->serialize($object, 'json', $context);
    }
}
