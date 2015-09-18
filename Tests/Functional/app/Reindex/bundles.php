<?php
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use FOS\ElasticaBundle\FOSElasticaBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return array(
    new DoctrineBundle(),
    new FOSElasticaBundle(),
    new FrameworkBundle(),
    new JMSSerializerBundle(),
);
