<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) FriendsOfSymfony <https://github.com/FriendsOfSymfony/FOSElasticaBundle/graphs/contributors>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Transformer;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class AbstractElasticaToModelTransformer implements ElasticaToModelTransformerInterface
{
    /**
     * PropertyAccessor instance.
     *
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Set the PropertyAccessor instance.
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Returns a sorting closure to be used with usort() to put retrieved objects
     * back in the order that they were returned by ElasticSearch.
     *
     * @param array $idPos
     * @param string $identifierPath
     * @return callable
     */
    protected function getSortingClosure(array $idPos, $identifierPath)
    {
        $propertyAccessor = $this->propertyAccessor;

        return function ($a, $b) use ($idPos, $identifierPath, $propertyAccessor) {
            return $idPos[$propertyAccessor->getValue($a, $identifierPath)] > $idPos[$propertyAccessor->getValue($b, $identifierPath)];
        };
    }
}
