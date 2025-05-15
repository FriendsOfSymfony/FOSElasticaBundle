<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine;

use FOS\ElasticaBundle\Doctrine\ConditionalUpdate;

class ConditionalUpdateEntity implements ConditionalUpdate
{
    public $identifier;
    private $id;
    private $shouldBeUpdated = true;

    public function __construct($id, $shouldBeUpdated = true)
    {
        $this->id = $id;
        $this->shouldBeUpdated = $shouldBeUpdated;
    }

    public function getId()
    {
        return $this->id;
    }

    public function shouldBeUpdated(): bool
    {
        return $this->shouldBeUpdated;
    }

    public function setShouldBeUpdated(bool $shouldBeUpdated): void
    {
        $this->shouldBeUpdated = $shouldBeUpdated;
    }
}
