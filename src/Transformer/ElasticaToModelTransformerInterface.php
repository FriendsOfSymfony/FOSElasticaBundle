<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Transformer;

use Elastica\Result;
use FOS\ElasticaBundle\HybridResult;

/**
 * Maps Elastica documents with model objects.
 *
 * @todo: introduce a template of getObjectClass() for transform() return type
 */
interface ElasticaToModelTransformerInterface
{
    /**
     * Transforms an array of elastica objects into an array of
     * model objects fetched from the doctrine repository.
     *
     * @param Result[] $elasticaObjects array of elastica objects
     *
     * @return array<object> of model objects
     */
    public function transform(array $elasticaObjects);

    /**
     * @param Result[] $elasticaObjects
     *
     * @return list<HybridResult>
     */
    public function hybridTransform(array $elasticaObjects);

    /**
     * Returns the object class used by the transformer.
     *
     * @return class-string
     */
    public function getObjectClass(): string;

    /**
     * Returns the identifier field from the options.
     */
    public function getIdentifierField(): string;
}
