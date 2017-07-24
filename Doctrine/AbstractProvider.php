<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Provider\AbstractProvider as BaseAbstractProvider;
use FOS\ElasticaBundle\Provider\IndexableInterface;

abstract class AbstractProvider extends BaseAbstractProvider
{
    /**
     * @var SliceFetcherInterface
     */
    private $sliceFetcher;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * Constructor.
     *
     * @param ObjectPersisterInterface $objectPersister
     * @param IndexableInterface       $indexable
     * @param string                   $objectClass
     * @param array                    $baseOptions
     * @param ManagerRegistry          $managerRegistry
     * @param SliceFetcherInterface    $sliceFetcher
     */
    public function __construct(
        ObjectPersisterInterface $objectPersister,
        IndexableInterface $indexable,
        $objectClass,
        array $baseOptions,
        ManagerRegistry $managerRegistry,
        SliceFetcherInterface $sliceFetcher = null
    ) {
        parent::__construct($objectPersister, $indexable, $objectClass, $baseOptions);

        $this->managerRegistry = $managerRegistry;
        $this->sliceFetcher = $sliceFetcher;
    }

    /**
     * Counts objects that would be indexed using the query builder.
     *
     * @param object $queryBuilder
     *
     * @return int
     */
    abstract protected function countObjects($queryBuilder);

    /**
     * Creates the query builder, which will be used to fetch objects to index.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return object
     */
    abstract protected function createQueryBuilder($method, array $arguments = []);

    /**
     * Fetches a slice of objects using the query builder.
     *
     * @param object $queryBuilder
     * @param int    $limit
     * @param int    $offset
     *
     * @return array
     */
    abstract protected function fetchSlice($queryBuilder, $limit, $offset);

    /**
     * {@inheritdoc}
     */
    protected function doPopulate($options, \Closure $loggerClosure = null)
    {
        $manager = $this->managerRegistry->getManagerForClass($this->objectClass);

        $queryBuilder = $this->createQueryBuilder($options['query_builder_method']);
        $limit = $nbObjects = $this->countObjects($queryBuilder);
        if ($options['limit'] && ($nbObjects - $options['offset']) > $options['limit']) {
            $limit = $options['limit'] + $options['offset'];
        }
        $offset = $options['offset'];
        if ($options['limit'] && $options['limit'] < $options['batch_size']) {
            $options['batch_size'] = $options['limit'];
        }

        $objects = [];
        for (; $offset < $limit; $offset += $options['batch_size']) {
            $sliceSize = $options['batch_size'];
            try {
                $objects = $this->getSlice($queryBuilder, $options['batch_size'], $offset, $objects);
                $sliceSize = count($objects);
                $objects = $this->filterObjects($options, $objects);

                if (!empty($objects)) {
                    $this->objectPersister->insertMany($objects);
                }
            } catch (BulkResponseException $e) {
                if (!$options['ignore_errors']) {
                    throw $e;
                }

                if (null !== $loggerClosure) {
                    $loggerClosure(
                        $options['batch_size'],
                        $nbObjects,
                        sprintf('<error>%s</error>', $e->getMessage())
                    );
                }
            }

            if ($options['clear_object_manager']) {
                $manager->clear();
            }

            usleep($options['sleep']);

            if (null !== $loggerClosure) {
                $loggerClosure($sliceSize, $nbObjects);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions()
    {
        parent::configureOptions();

        $this->resolver->setDefaults([
            'clear_object_manager' => true,
            'debug_logging' => false,
            'ignore_errors' => false,
            'offset' => 0,
            'query_builder_method' => 'createQueryBuilder',
            'sleep' => 0,
        ]);
    }

    /**
     * If this Provider has a SliceFetcher defined, we use it instead of falling back to
     * the fetchSlice methods defined in the ORM/MongoDB subclasses.
     *
     * @param $queryBuilder
     * @param int   $limit
     * @param int   $offset
     * @param array $lastSlice
     *
     * @return array
     */
    private function getSlice($queryBuilder, $limit, $offset, $lastSlice)
    {
        if (!$this->sliceFetcher) {
            return $this->fetchSlice($queryBuilder, $limit, $offset);
        }

        $manager = $this->managerRegistry->getManagerForClass($this->objectClass);
        $identifierFieldNames = $manager
            ->getClassMetadata($this->objectClass)
            ->getIdentifierFieldNames();

        return $this->sliceFetcher->fetch(
            $queryBuilder,
            $limit,
            $offset,
            $lastSlice,
            $identifierFieldNames
        );
    }
}
