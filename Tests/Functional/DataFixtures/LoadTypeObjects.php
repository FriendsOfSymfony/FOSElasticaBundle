<?php
namespace FOS\ElasticaBundle\Tests\Functional\DataFixtures;

// use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\ElasticaBundle\Tests\Functional\TypeObj;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTypeObjects implements ContainerAwareInterface
// , FixtureInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        for ($i = 0; $i < 10; ++$i) {
            $type = new TypeObj();
            $type->id = $i;
            $type->coll = $i + 1;
            $type->field1 = $i + 2;
            $type->field2 = $i + 3;
            $manager->persist($type);
        }
        $manager->flush();
    }
}
