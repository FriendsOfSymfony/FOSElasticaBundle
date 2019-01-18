<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Transformer;

use FOS\ElasticaBundle\Transformer\AbstractElasticaToModelTransformer;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use FOS\ElasticaBundle\Tests\Unit\UnitTestHelper;

class AbstractElasticaToModelTransformerTest extends UnitTestHelper
{
    public function testSetPropertyAccessor()
    {
        $propertyAccessor = $this->mockPropertyAccesor();
        $transformer = $this->mockAbstractElasticaToModelTransformer();
        $transformer->setPropertyAccessor($propertyAccessor);
        $this->assertEquals($propertyAccessor, $this->getProtectedProperty($transformer, 'propertyAccessor'));
    }

    protected function mockAbstractElasticaToModelTransformer()
    {
        $mock = $this
            ->getMockBuilder(AbstractElasticaToModelTransformer::class)
            ->getMockForAbstractClass();
        return $mock;
    }

    protected function mockPropertyAccesor()
    {
        $mock = $this->createMock(PropertyAccessorInterface::class);
        return $mock;
    }
}
