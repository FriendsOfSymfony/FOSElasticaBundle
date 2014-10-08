<?php

use FOS\ElasticaBundle\FOSElasticaBundle;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

return array(
    new FrameworkBundle(),
    new FOSElasticaBundle(),
    new KnpPaginatorBundle(),
    new TwigBundle(),
);
