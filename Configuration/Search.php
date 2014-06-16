<?php

/**
 * This file is part of the FOSElasticaBundle project.
 *
 * (c) Tim Nagel <tim@nagel.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Configuration;

use FOS\ElasticaBundle\Annotation\Search as BaseSearch;

/**
 * Annotation class for setting search repository.
 *
 * @Annotation
 * @deprecated Use FOS\ElasticaBundle\Annotation\Search instead
 * @Target("CLASS")
 */
class Search extends BaseSearch
{
} 
