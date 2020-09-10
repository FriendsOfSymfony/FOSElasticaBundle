<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Unit\Doctrine\PHPCR;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\ElasticaBundle\Doctrine\PHPCR\ElasticaToModelTransformer;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentRepository;
use PHPUnit\Framework\TestCase;

class ElasticaToModelTransformerTest extends TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var DocumentRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    protected $objectClass = 'stdClass';

    protected function setUp(): void
    {
        if (!class_exists(DocumentManager::class)) {
            $this->markTestSkipped('Doctrine PHPCR is not present');
        }

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->manager = $this->createMock(DocumentManager::class);

        $this->registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $this->repository = $this
            ->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(array(
                'customQueryBuilderCreator',
                'createQueryBuilder',
                'find',
                'findAll',
                'findBy',
                'findOneBy',
                'getClassName',
                'findMany'
            ))->getMock();

        $this->repository->expects($this->any())
            ->method('findMany')
            ->will($this->returnValue(new ArrayCollection([new \stdClass(), new \stdClass()])));

        $this->manager->expects($this->any())
            ->method('getRepository')
            ->with($this->objectClass)
            ->will($this->returnValue($this->repository));
    }

    public function testTransformUsesFindByIdentifier()
    {
        $this->registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $transformer = new ElasticaToModelTransformer($this->registry, $this->objectClass);

        $class = new \ReflectionClass(ElasticaToModelTransformer::class);
        $method = $class->getMethod('findByIdentifiers');
        $method->setAccessible(true);

        $method->invokeArgs($transformer, [
            ['c8f23994-d897-4c77-bcc3-bc6910e52a34', 'f1083287-a67e-480e-a426-e8427d00eae4'],
            $this->objectClass,
        ]);
    }
}
