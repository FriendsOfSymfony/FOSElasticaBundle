<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

/**
 * Interface Index config interface.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
interface IndexConfigInterface
{
    public function getElasticSearchName(): string;

    public function getModel(): ?string;

    public function getName(): string;

    public function getSettings(): array;

    public function getDateDetection(): ?bool;

    public function getDynamicDateFormats(): ?array;

    public function getAnalyzer(): ?string;

    public function getMapping(): array;

    public function getNumericDetection(): ?bool;

    /**
     * @return string|bool|null
     */
    public function getDynamic();
}
