<?php

namespace FOS\ElasticaBundle\Finder;

use Pagerfanta\Pagerfanta;

interface PaginatedFinderInterface
{
	/**
	 * Searches for query results and returns them wrapped in a paginator
	 *
	 * @param mixed $query  Can be a string, an array or an Elastica_Query object
	 * @return Pagerfanta paginated results
	 */
	function findPaginated($query);
}
