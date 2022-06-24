<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @phpstan-import-type TFields from ModelToElasticaAutoTransformer
 */
abstract class AbstractTransformEvent extends Event
{
    /**
     * @var Document
     */
    protected $document;

    /**
     * @var array
     * @phpstan-var TFields
     */
    private $fields;

    /**
     * @var object
     */
    private $object;

    /**
     * @phpstan-param TFields $fields
     */
    public function __construct(Document $document, array $fields, object $object)
    {
        $this->document = $document;
        $this->fields = $fields;
        $this->object = $object;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @phpstan-return TFields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getObject(): object
    {
        return $this->object;
    }
}
