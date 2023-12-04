<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Message\AsyncPersistPage;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderRegistry;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @phpstan-import-type TPagerPersisterOptions from PagerPersisterInterface
 */
final class AsyncPagerPersister implements PagerPersisterInterface
{
    public const NAME = 'async';
    private const DEFAULT_PAGE_SIZE = 100;

    /**
     * @var PagerPersisterRegistry
     */
    private $pagerPersisterRegistry;

    /**
     * @var PagerProviderRegistry
     */
    private $pagerProviderRegistry;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        PagerPersisterRegistry $pagerPersisterRegistry,
        PagerProviderRegistry $pagerProviderRegistry,
        MessageBusInterface $messageBus
    ) {
        $this->pagerPersisterRegistry = $pagerPersisterRegistry;
        $this->pagerProviderRegistry = $pagerProviderRegistry;
        $this->messageBus = $messageBus;
    }

    public function insert(PagerInterface $pager, array $options = []): void
    {
        $pager->setMaxPerPage(empty($options['max_per_page']) ? self::DEFAULT_PAGE_SIZE : $options['max_per_page']);

        $options = \array_replace([
            'max_per_page' => $pager->getMaxPerPage(),
            'first_page' => $pager->getCurrentPage(),
            'last_page' => $pager->getNbPages(),
        ], $options);

        $pager->setCurrentPage($options['first_page']);

        $lastPage = \min($options['last_page'], $pager->getNbPages());
        $page = $pager->getCurrentPage();
        do {
            $this->messageBus->dispatch(new AsyncPersistPage($page, $options));

            ++$page;
        } while ($page <= $lastPage);
    }

    /**
     * @phpstan-param TPagerPersisterOptions $options
     */
    public function insertPage(int $page, array $options = []): void
    {
        if (!isset($options['indexName'])) {
            throw new \RuntimeException('Invalid call. $options is missing the indexName key.');
        }
        if (!isset($options['max_per_page'])) {
            throw new \RuntimeException('Invalid call. $options is missing the max_per_page key.');
        }

        $options['first_page'] = $page;
        $options['last_page'] = $page;

        $provider = $this->pagerProviderRegistry->getProvider($options['indexName']);
        $pager = $provider->provide($options);
        $pager->setMaxPerPage($options['max_per_page']);
        $pager->setCurrentPage($options['first_page']);

        /** @var InPlacePagerPersister $pagerPersister */
        $pagerPersister = $this->pagerPersisterRegistry->getPagerPersister(InPlacePagerPersister::NAME);
        $pagerPersister->insert($pager, $options);
    }
}
