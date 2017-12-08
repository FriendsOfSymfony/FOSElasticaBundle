<?php

use FOS\ElasticaBundle\FOSElasticaBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

return array(
    new FrameworkBundle(),
    new FOSElasticaBundle(),
    new TwigBundle(),
);
