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

use Elastica\Document;

/**
 * Maps Elastica documents with model objects.
 *
 * @phpstan-type TFields = array<string, TPath|TAttachment|TProperties>
 * @phpstan-type TPath = array{property_path?: non-empty-string|false}
 * @phpstan-type TAttachment = array{type: 'attachment'}
 * @phpstan-type TProperties = array{
 *     type: 'nested'|'object',
 *     properties: array<string, TPath|TAttachment|TPropertiesNested>
 * }
 * @phpstan-type TPropertiesNested = array{
 *     type: 'nested'|'object',
 *     properties: array<string, TPath|TAttachment|array<string, mixed>>
 * }
 */
interface ModelToElasticaTransformerInterface
{
    /**
     * Transforms an object into an elastica object having the required keys.
     *
     * @phpstan-param TFields $fields
     */
    public function transform(object $object, array $fields): Document;
}
