<?php

namespace FOQ\ElasticaBundle\Provider;

use RuntimeException;

/**
 * Skip a document during population
 */
class NotIndexableException extends RuntimeException
{

}
