<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional\app\ORM;

class IndexableService
{
    public function isIndexable()
    {
        return true;
    }

    public static function isntIndexable()
    {
        return false;
    }
}
