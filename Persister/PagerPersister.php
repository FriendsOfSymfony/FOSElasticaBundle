<?php

namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Provider\Indexable;
use FOS\ElasticaBundle\Provider\PagerInterface;
use Pagerfanta\Pagerfanta;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;

class PagerPersister implements PagerPersisterInterface
{
    /**
     * @var Indexable
     */
    private $indexable;

    /**
     * @var PersisterRegistry
     */
    private $persisterRegistry;

    /**
     * @param Indexable $indexable
     * @param PersisterRegistry $persisterRegistry
     */
    public function __construct(Indexable $indexable, PersisterRegistry $persisterRegistry)
    {
        $this->indexable = $indexable;
        $this->persisterRegistry = $persisterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(PagerInterface $pager, \Closure $loggerClosure = null, array $options = array())
    {
        $nbObjects = $pager->getNbResults();
        $objectPersister = $this->persisterRegistry->getPersister($options['indexName'], $options['typeName']);

        $pager->setMaxPerPage($options['batch_size']);

        $page = $pager->getCurrentPage();
        for(;$page <= $pager->getNbPages(); $page++) {
            $pager->setCurrentPage($page);

            $sliceSize = $options['batch_size'];

            try {
                $objects = $pager->getCurrentPageResults();
                $sliceSize = count($objects);
                $objects = $this->filterObjects($options, $objects);

                if (!empty($objects)) {
                    $objectPersister->insertMany($objects);
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

            if (null !== $loggerClosure) {
                $loggerClosure($sliceSize, $nbObjects);
            }

            usleep($options['sleep']);
        }
    }

    /**
     * Filters objects away if they are not indexable.
     *
     * @param array $options
     * @param array $objects
     * @return array
     */
    protected function filterObjects(array $options, $objects)
    {
        if (!is_iterable($objects)) {
            throw new \LogicException(sprintf(
                'The objects argument must be iterable but got "%s"',
                is_object($objects) ? get_class($objects) : gettype($objects)
            ));
        }

        if ($options['skip_indexable_check']) {
            return $objects;
        }

        $index = $options['indexName'];
        $type = $options['typeName'];

        $return = array();
        foreach ($objects as $object) {
            if (!$this->indexable->isObjectIndexable($index, $type, $object)) {
                continue;
            }

            $return[] = $object;
        }

        return $return;
    }
}
