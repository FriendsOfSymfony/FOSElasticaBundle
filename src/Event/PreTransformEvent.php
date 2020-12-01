<?php

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Event;

use Elastica\Document;

final class PreTransformEvent extends AbstractTransformEvent
{
    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }
}
