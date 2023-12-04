<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

use Pagerfanta\Pagerfanta;

/**
 * @template T
 */
class PagerfantaPager implements PagerInterface
{
    /**
     * @var Pagerfanta<T>
     */
    private $pagerfanta;

    /**
     * @param Pagerfanta<T> $pagerfanta
     */
    public function __construct(Pagerfanta $pagerfanta)
    {
        $this->pagerfanta = $pagerfanta;
    }

    public function getNbResults(): int
    {
        return $this->pagerfanta->getNbResults();
    }

    public function getNbPages(): int
    {
        return $this->pagerfanta->getNbPages();
    }

    public function getCurrentPage(): int
    {
        return $this->pagerfanta->getCurrentPage();
    }

    public function setCurrentPage(int $page)
    {
        $this->pagerfanta->setCurrentPage($page);
    }

    public function getMaxPerPage(): int
    {
        return $this->pagerfanta->getMaxPerPage();
    }

    public function setMaxPerPage(int $perPage)
    {
        $this->pagerfanta->setMaxPerPage($perPage);
    }

    /**
     * @phpstan-return iterable<array-key, T>
     */
    public function getCurrentPageResults()
    {
        return $this->pagerfanta->getCurrentPageResults();
    }

    /**
     * @return Pagerfanta<T>
     */
    public function getPagerfanta(): Pagerfanta
    {
        return $this->pagerfanta;
    }
}
