<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional\app\ORM;

class IndexableService
{
    public function isIndexable($object)
    {
        return true;
    }

    public static function isntIndexable($object)
    {
        return false;
    }
}
