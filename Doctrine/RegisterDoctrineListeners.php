<?php
namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Persister\Event\Events;
use JMS\Serializer\EventDispatcher\EventDispatcherInterface;

class RegisterDoctrineListeners
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function register(ObjectManager $manager, array $options)
    {
        $options = array_replace([
            'clear_object_manager' => true,
            'debug_logging'        => false,
            'sleep'                => 0,
        ], $options);

        if ($options['clear_object_manager']) {
            $this->dispatcher->addListener(Events::POST_PERSIST, function() use ($manager) {
                $manager->clear();
            });
        }

        if ($options['sleep']) {
            $this->dispatcher->addListener(Events::POST_PERSIST, function() use ($options) {
                usleep($options['sleep']);
            });
        }

        if ($options['debug_logging'] && $manager instanceof EntityManagerInterface) {
            $configuration = $manager->getConnection()->getConfiguration();
            $logger = $configuration->getSQLLogger();

            $this->dispatcher->addListener(Events::PRE_FETCH_OBJECTS, function() use ($configuration) {
                $configuration->setSQLLogger(null);
            });

            $this->dispatcher->addListener(Events::PRE_INSERT_OBJECTS, function() use ($configuration, $logger) {
                $configuration->setSQLLogger($logger);
            });
        }

        if ($options['debug_logging'] && $manager instanceof DocumentManager) {
            $configuration = $manager->getConnection()->getConfiguration();
            $logger = $configuration->getLoggerCallable();

            $this->dispatcher->addListener(Events::PRE_FETCH_OBJECTS, function() use ($configuration) {
                $configuration->setLoggerCallable(null);
            });

            $this->dispatcher->addListener(Events::PRE_INSERT_OBJECTS, function() use ($configuration, $logger) {
                $configuration->setLoggerCallable($logger);
            });
        }

        usleep($options['sleep']);
    }
}
