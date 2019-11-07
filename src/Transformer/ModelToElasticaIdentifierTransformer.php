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
 * Creates an Elastica document with the ID of
 * the Doctrine object as Elastica document ID.
 */
class ModelToElasticaIdentifierTransformer extends ModelToElasticaAutoTransformer
{
    /**
     * Creates an elastica document with the id of the doctrine object as id.
     **/
    public function transform(object $object, array $fields): Document
    {
        $identifier = $this->propertyAccessor->getValue($object, $this->options['identifier']);

        return new Document($identifier);
    }
}
