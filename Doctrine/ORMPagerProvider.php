<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

final class ORMPagerProvider implements PagerProviderInterface
{
    const ENTITY_ALIAS = 'a';

    /**
     * @var string
     */
    private $objectClass;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var array
     */
    private $baseOptions;

    /**
     * @param ManagerRegistry $doctrine
     * @param string          $objectClass
     * @param array           $baseOptions
     */
    public function __construct(ManagerRegistry $doctrine, $objectClass, array $baseOptions)
    {
        $this->doctrine = $doctrine;
        $this->objectClass = $objectClass;
        $this->baseOptions = $baseOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function provide(array $options = array())
    {
        $options = array_replace($this->baseOptions, $options);
        
        $adapter = new DoctrineORMAdapter($this->createQueryBuilder($options['query_builder_method']));

        return new PagerfantaPager(new Pagerfanta($adapter));
    }

    /**
     * {@inheritdoc}
     */
    private function createQueryBuilder($method)
    {
        $repository = $this->doctrine
            ->getManagerForClass($this->objectClass)
            ->getRepository($this->objectClass);

        return call_user_func([$repository, $method], self::ENTITY_ALIAS);
    }
}
