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

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooser;
use Doctrine\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use Pagerfanta\Doctrine\PHPCRODM\QueryAdapter;
use Pagerfanta\Pagerfanta;

final class PHPCRPagerProvider implements PagerProviderInterface
{
    public const ENTITY_ALIAS = 'a';

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

    public function provide(array $options = []): PagerInterface
    {
        $options = \array_replace($this->baseOptions, $options);

        /** @var DocumentManagerInterface $manager */
        $manager = $this->doctrine->getManagerForClass($this->objectClass);
        if (isset($options['locale'])) {
            /** @var LocaleChooser $localeChooser */
            $localeChooser = $manager->getLocaleChooserStrategy();
            $localeChooser->setLocale($options['locale']);
            $manager->setLocaleChooserStrategy($localeChooser);
        }
        $repository = $manager->getRepository($this->objectClass);

        $adapter = new QueryAdapter(
            \call_user_func([$repository, $options['query_builder_method']], self::ENTITY_ALIAS)
        );

        $pager = new PagerfantaPager(new Pagerfanta($adapter));

        $this->registerListenersService->register($manager, $pager, $options);

        return $pager;
    }
}
