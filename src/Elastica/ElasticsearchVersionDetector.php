<?php

declare(strict_types=1);

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Elastica;

/**
 * Static probes for the installed Elastica library's API generation.
 *
 * Despite the name, the detection here is about the ruflin/elastica PHP library
 * (Elastica 8 vs Elastica 9), not the Elasticsearch server. The two move
 * roughly in lockstep but the runtime decisions the bundle has to make
 * (which template-API shape to produce, which endpoint to hit) are driven by
 * the client library, not the server.
 */
final class ElasticsearchVersionDetector
{
    private function __construct()
    {
    }

    /**
     * Returns true when the active Elastica's IndexTemplate exclusively hits the
     * new `_index_template` API (body must wrap `mappings`/`settings` under a
     * `template` key). False when it defaults to (or only supports) the legacy
     * `_template` API.
     *
     * Detection uses \Elastica\Request as the marker: it ships in every 8.x
     * release and was removed in 9.0 (documented in Elastica's UPGRADE-9.0.md
     * under "Removed Classes"). Cleaner than property/version probes — the
     * class boundary precisely matches the major-version boundary.
     */
    public static function usesNewIndexTemplateApi(): bool
    {
        return !\class_exists(\Elastica\Request::class);
    }
}
