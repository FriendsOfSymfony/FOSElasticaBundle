<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use FOS\ElasticaBundle\FOSElasticaBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use JMS\SerializerBundle\JMSSerializerBundle;

return array(
    new FrameworkBundle(),
    new FOSElasticaBundle(),
    new DoctrineBundle(),
);
