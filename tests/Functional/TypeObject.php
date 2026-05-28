<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

class TypeObject
{
    public $id = 5;
    public $coll;
    public $field1;
    public $field2;
    public $field3;

    public function isIndexable(): bool
    {
        return true;
    }

    public function isntIndexable(): bool
    {
        return false;
    }

    public function getSerializableColl(): array
    {
        return \iterator_to_array($this->coll, false);
    }
}
