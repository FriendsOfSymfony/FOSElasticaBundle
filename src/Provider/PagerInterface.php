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

interface PagerInterface
{
    /**
     * @return int
     */
    public function getNbResults();

    /**
     * @return int
     */
    public function getNbPages();

    /**
     * @return int
     */
    public function getCurrentPage();

    /**
     * @param int $page
     */
    public function setCurrentPage($page);

    /**
     * @return int
     */
    public function getMaxPerPage();

    /**
     * @param int $perPage
     */
    public function setMaxPerPage($perPage);

    /**
     * @return array
     */
    public function getCurrentPageResults();
}
