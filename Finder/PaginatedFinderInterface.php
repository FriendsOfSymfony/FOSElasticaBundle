<?php

namespace FOQ\ElasticaBundle\Finder;

use Zend\Paginator\Paginator;

interface PaginatedFinderInterface
{
	/**
	 * Searches for query results and returns them wrapped in a paginator
	 *
	 * @param mixed $query  Can be a string, an array or an Elastica_Query object
	 * @return Paginator paginated results
	 */
	function findPaginated($query);
}
