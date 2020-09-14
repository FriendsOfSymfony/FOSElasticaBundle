<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Provider;

use Pagerfanta\Pagerfanta;

class PagerfantaPager implements PagerInterface
{
    /**
     * @var Pagerfanta
     */
    private $pagerfanta;

    public function __construct(Pagerfanta $pagerfanta)
    {
        $this->pagerfanta = $pagerfanta;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults(): int
    {
        return $this->pagerfanta->getNbResults();
    }

    /**
     * {@inheritdoc}
     */
    public function getNbPages(): int
    {
        return $this->pagerfanta->getNbPages();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage(): int
    {
        return $this->pagerfanta->getCurrentPage();
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentPage(int $page)
    {
        $this->pagerfanta->setCurrentPage($page);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPerPage(): int
    {
        return $this->pagerfanta->getMaxPerPage();
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPerPage(int $perPage)
    {
        $this->pagerfanta->setMaxPerPage($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPageResults()
    {
        return $this->pagerfanta->getCurrentPageResults();
    }

    public function getPagerfanta(): Pagerfanta
    {
        return $this->pagerfanta;
    }
}
