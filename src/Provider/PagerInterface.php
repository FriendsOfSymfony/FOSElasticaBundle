<?php

declare(strict_types=1);

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

    public function setCurrentPage(int $page): void;

    public function getMaxPerPage(): int;

    public function setMaxPerPage(int $perPage): void;

    /**
     * @return array<object>|\Traversable<object>
     */
    public function getCurrentPageResults(): iterable;
}
