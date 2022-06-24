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

interface PagerInterface
{
    public function getNbResults(): int;

    public function getNbPages(): int;

    public function getCurrentPage(): int;

    /**
     * @return void
     */
    public function setCurrentPage(int $page);

    public function getMaxPerPage(): int;

    /**
     * @return void
     */
    public function setMaxPerPage(int $perPage);

    /**
     * @return array<object>|\Traversable<object>
     */
    public function getCurrentPageResults();
}
