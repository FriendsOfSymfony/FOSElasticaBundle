<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine\PHPCR;

use FOS\ElasticaBundle\Doctrine\AbstractElasticaToModelTransformer;

/**
 * Maps Elastica documents with Doctrine objects
 * This mapper assumes an exact match between
 * elastica documents ids and doctrine object ids.
 */
class ElasticaToModelTransformer extends AbstractElasticaToModelTransformer
{
    /**
     * Fetch objects for theses identifier values.
     *
     * @param array $identifierValues ids values
     * @param bool  $hydrate          whether or not to hydrate the objects, false returns arrays
     *
     * @return array of objects or arrays
     */
    protected function findByIdentifiers(array $identifierValues, bool $hydrate): array
    {
        // @phpstan-ignore-next-line The call is \Doctrine\ODM\PHPCR\DocumentRepository::findMany()
        $documents = $this->registry
            ->getManager()
            ->getRepository($this->objectClass)
            ->findMany($identifierValues)
        ;

        // phpcr-odm < 3.0 returns a Collection, 3.0+ returns an array.
        return \is_array($documents) ? $documents : $documents->toArray();
    }
}
