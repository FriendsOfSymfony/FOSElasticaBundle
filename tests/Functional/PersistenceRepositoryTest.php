<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

class PersistenceRepositoryTest extends WebTestCase
{
    public function testRepositoryShouldBeSetCorrectly()
    {
        static::bootKernel(['test_case' => 'ORM']);

        $repository = static::$kernel->getContainer()->get('fos_elastica.manager.orm')
            ->getRepository(TypeObject::class);

        $this->assertInstanceOf(TypeObjectRepository::class, $repository);
    }
}
