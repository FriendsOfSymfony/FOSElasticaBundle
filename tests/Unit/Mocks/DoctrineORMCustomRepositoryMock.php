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

namespace FOS\ElasticaBundle\Tests\Unit\Mocks;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class DoctrineORMCustomRepositoryMock extends EntityRepository
{
    public function createCustomQueryBuilder(): ?QueryBuilder
    {
        return null;
    }
}
