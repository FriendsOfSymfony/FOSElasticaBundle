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
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Provider\DoctrinePaginatorPager;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;

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
     * @var RegisterListenersService
     */
    private $registerListenersService;

    /**
     * @param ManagerRegistry $doctrine
     * @param RegisterListenersService $registerListenersService
     * @param string $objectClass
     * @param array $baseOptions
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
    public function provide(array $options = array())
    {
        $options = array_replace($this->baseOptions, $options);

        $manager = $this->doctrine->getManagerForClass($this->objectClass);
        $repository = $manager->getRepository($this->objectClass);

        $qb = \call_user_func([$repository, $options['query_builder_method']], self::ENTITY_ALIAS);

        // Ensure that the query builder has a sorting configured. Without a ORDER BY clause, the SQL standard does not
        // guarantee any order, which breaks the pagination (second page might use a different sorting that when retrieving
        // the first page).
        // If the QueryBuilder already has its own ordering, or the method returned a Query instead of a QueryBuilder, we
        // assume that the query already provides a proper sorting. This allows giving full control over sorting if wanted
        // when using a custom method.
        if ($qb instanceof QueryBuilder && empty($qb->getDQLPart('orderBy'))) {
            // When getting root aliases, the QueryBuilder normalizes all from parts to From objects, in case they were added as string using the low-level API.
            // This side-effect allows us to be sure to get only From objects in the next call.
            $qb->getRootAliases();

            /** @var From[] $fromClauses */
            $fromClauses = $qb->getDQLPart('from');

            foreach ($fromClauses as $fromClause) {
                $identifiers = $manager->getClassMetadata($fromClause->getFrom())->getIdentifierFieldNames();

                foreach ($identifiers as $identifier) {
                    $qb->addOrderBy($fromClause->getAlias().'.'.$identifier);
                }
            }
        }

        $pager = new DoctrinePaginatorPager($qb->getQuery());

        $this->registerListenersService->register($manager, $pager, $options);

        return $pager;
    }
}
