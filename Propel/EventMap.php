<?php

namespace FOS\ElasticaBundle\Propel;

/**
 * Simple class with mapped event, for use in DependencyInjection.
 * @author Arkadiusz Dzięgiel <arkadiusz.dziegiel@glorpen.pl>
 */
class EventMap
{
    const postPersist = 'model.insert.post';
    const postUpdate = 'model.update.post';
    const preRemove = 'model.delete.pre';
    const postFlush = 'connection.commit.post';
}
