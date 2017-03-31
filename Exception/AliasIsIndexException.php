<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Exception;

class AliasIsIndexException extends \Exception
{
    /**
     * @param string $indexName
     */
    public function __construct($indexName)
    {
        parent::__construct(sprintf('Expected %s to be an alias but it is an index.', $indexName));
    }
}
