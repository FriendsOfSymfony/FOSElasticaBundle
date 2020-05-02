<?php

namespace FOS\ElasticaBundle\Provider;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DoctrinePaginatorPager implements PagerInterface
{
    /**
     * @var Query
     */
    private $query;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var int
     */
    private $currentPage = 1;

    /**
     * @var int
     */
    private $maxPerPage = 100;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getNbResults()
    {
        return $this->getPaginator()->count();
    }

    public function getNbPages()
    {
        return ceil($this->getPaginator()->count() / $this->getMaxPerPage());
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function setCurrentPage($page)
    {
        $this->currentPage = (int) $page;
    }

    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    public function setMaxPerPage($perPage)
    {
        $this->maxPerPage = (int) $perPage;
    }

    public function getCurrentPageResults()
    {
        $firstResult = $this->maxPerPage * ($this->currentPage - 1);

        $this->query
            ->setFirstResult($firstResult)
            ->setMaxResults($this->maxPerPage);

        return $this->getPaginator()->getIterator();
    }

    private function getPaginator(): Paginator
    {
        if (! isset($this->paginator)) {
            $this->paginator = new Paginator($this->query);
        }

        return $this->paginator;
    }
}
