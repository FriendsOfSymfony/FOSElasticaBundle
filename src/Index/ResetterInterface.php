<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Index;

/**
 * Interface Resetter interface.
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
interface ResetterInterface
{
    /**
     * Reset all indexes.
     *
     * @return void
     */
    public function resetAllIndexes();

    /**
     * Reset index.
     *
     * @return void
     */
    public function resetIndex(string $indexName);
}
