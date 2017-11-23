<?php
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
    public function getNbResults()
    {
        return $this->pagerfanta->getNbResults(); 
    }

    /**
     * {@inheritdoc}
     */
    public function getNbPages()
    {
        return $this->pagerfanta->getNbPages();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage()
    {
        return $this->pagerfanta->getCurrentPage();
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentPage($page)
    {
        $this->pagerfanta->setCurrentPage($page);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPerPage()
    {
        return $this->pagerfanta->getMaxPerPage();
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPerPage($perPage)
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
}
