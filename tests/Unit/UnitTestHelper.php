<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit;

use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Elastica\SearchableInterface;
use Elastica\ResultSet;
use PHPUnit\Framework\TestCase;

class UnitTestHelper extends TestCase
{
    /**
     * Gets a protected property on a given object via reflection.
     *
     * @param object $object   instance in which protected value is being modified
     * @param string $property property on instance being modified
     */
    protected function getProtectedProperty($object, string $property)
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    protected function mockElasticaToModelTransformer()
    {
        $mock = $this
            ->getMockBuilder(ElasticaToModelTransformerInterface::class)
            ->getMock();
        return $mock;
    }

    protected function mockSearchable()
    {
        $mock = $this
            ->getMockBuilder(SearchableInterface::class)
            ->getMock();
        return $mock;
    }

    protected function mockResultSet()
    {
        $mock = $this
            ->getMockBuilder(ResultSet::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $mock;
    }
}
