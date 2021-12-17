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

/**
 * @internal
 */
class PersistenceRepositoryTest extends WebTestCase
{
    public function testRepositoryShouldBeSetCorrectly()
    {
        self::bootKernel(['test_case' => 'ORM']);

        $repository = $this->getContainerBC()->get('test_alias.fos_elastica.manager.orm')
            ->getRepository(TypeObject::class)
        ;

        $this->assertInstanceOf(TypeObjectRepository::class, $repository);
    }
}
