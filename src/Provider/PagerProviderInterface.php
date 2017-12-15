<?php
namespace FOS\ElasticaBundle\Provider;

interface PagerProviderInterface
{
    /**
     * @param array    $options
     *
     * @return PagerInterface
     */
    public function provide(array $options = array());
}
