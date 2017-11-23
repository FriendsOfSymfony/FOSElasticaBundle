<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Propel;

use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use Pagerfanta\Adapter\PropelAdapter;
use Pagerfanta\Pagerfanta;

class Propel1PagerProvider implements PagerProviderInterface
{
    /**
     * @var string
     */
    private $objectClass;
    
    /**
     * @var array
     */
    private $baseOptions;

    /**
     * @param string $objectClass
     * @param array $baseOptions
     */
    public function __construct($objectClass, array $baseOptions)
    {
        $this->objectClass = $objectClass;
        $this->baseOptions = $baseOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function provide(array $options = array())
    {
        $options = array_replace($this->baseOptions, $options);
        
        $queryClass = $this->objectClass.'Query';

        $adapter = new PropelAdapter($queryClass::create());

        return new PagerfantaPager(new Pagerfanta($adapter));
    }
}
