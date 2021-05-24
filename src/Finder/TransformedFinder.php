<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Finder;

use Elastica\Query;
use Elastica\SearchableInterface;
use FOS\ElasticaBundle\Paginator\FantaPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\HybridPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\RawPaginatorAdapter;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;
use Pagerfanta\Pagerfanta;

/**
 * Finds elastica documents and map them to persisted objects.
 */
class TransformedFinder implements PaginatedFinderInterface
{
    /**
     * @var SearchableInterface
     */
    protected $searchable;

    /**
     * @var ElasticaToModelTransformerInterface
     */
    protected $transformer;

    public function __construct(SearchableInterface $searchable, ElasticaToModelTransformerInterface $transformer)
    {
        $this->searchable = $searchable;
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function find($query, ?int $limit = null, array $options = [])
    {
        $results = $this->search($query, $limit, $options);

        return $this->transformer->transform($results);
    }

    /**
     * @param $query
     *
     * @return array
     */
    public function findHybrid($query, ?int $limit = null, array $options = [])
    {
        $results = $this->search($query, $limit, $options);

        return $this->transformer->hybridTransform($results);
    }

    /**
     * @param $query
     */
    public function findRaw($query, ?int $limit = null, array $options = []): array
    {
        return $this->search($query, $limit, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function findPaginated($query, array $options = [])
    {
        $paginatorAdapter = $this->createPaginatorAdapter($query, $options);

        return new Pagerfanta(new FantaPaginatorAdapter($paginatorAdapter));
    }

    /**
     * Searches for query hybrid results and returns them wrapped in a paginator.
     *
     * @param mixed $query Can be a string, an array or an \Elastica\Query object
     *
     * @return Pagerfanta paginated hybrid results
     */
    public function findHybridPaginated($query, array $options = [])
    {
        $paginatorAdapter = $this->createHybridPaginatorAdapter($query, $options);

        return new Pagerfanta(new FantaPaginatorAdapter($paginatorAdapter));
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginatorAdapter($query, array $options = [])
    {
        $query = Query::create($query);

        return new TransformedPaginatorAdapter($this->searchable, $query, $options, $this->transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function createHybridPaginatorAdapter($query, array $options = [])
    {
        $query = Query::create($query);

        return new HybridPaginatorAdapter($this->searchable, $query, $options, $this->transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function createRawPaginatorAdapter($query, array $options = [])
    {
        $query = Query::create($query);

        return new RawPaginatorAdapter($this->searchable, $query, $options);
    }

    /**
     * @param $query
     *
     * @return array
     */
    protected function search($query, ?int $limit = null, array $options = [])
    {
        $queryObject = Query::create($query);
        if (null !== $limit) {
            $queryObject->setSize($limit);
        }

        return $this->searchable->search($queryObject, $options)->getResults();
    }
}
