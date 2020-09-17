<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Pagerfanta\Pagerfanta;

final class MongoDBPagerProvider implements PagerProviderInterface
{
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
     * @var RegisterListenersService
     */
    private $registerListenersService;

    /**
     * @param string $objectClass
     */
    public function __construct(ManagerRegistry $doctrine, RegisterListenersService $registerListenersService, $objectClass, array $baseOptions)
    {
        $this->doctrine = $doctrine;
        $this->objectClass = $objectClass;
        $this->baseOptions = $baseOptions;
        $this->registerListenersService = $registerListenersService;
    }

    /**
     * {@inheritdoc}
     */
    public function provide(array $options = []): PagerInterface
    {
        $options = array_replace($this->baseOptions, $options);

        $manager = $this->doctrine->getManagerForClass($this->objectClass);
        $repository = $manager->getRepository($this->objectClass);

        $pager = new PagerfantaPager(new Pagerfanta(
            new DoctrineODMMongoDBAdapter(call_user_func([$repository, $options['query_builder_method']]))
        ));

        $this->registerListenersService->register($manager, $pager, $options);

        return $pager;
    }
}
