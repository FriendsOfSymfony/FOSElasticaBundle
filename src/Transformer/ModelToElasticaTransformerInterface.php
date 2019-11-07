<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Transformer;

use Elastica\Document;

/**
 * Maps Elastica documents with model objects.
 */
interface ModelToElasticaTransformerInterface
{
    /**
     * Transforms an object into an elastica object having the required keys.
     **/
    public function transform(object $object, array $fields): Document;
}
