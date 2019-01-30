<?php
namespace FOS\ElasticaBundle\Index;

/**
 * Interface Resetter interface
 *
 * @author Dmitry Balabka <dmitry.balabka@intexsys.lv>
 */
interface ResetterInterface
{
    /**
     * Reset all indexes
     *
     * @return void
     */
    public function resetAllIndexes();

    /**
     * Reset index
     *
     * @param string $indexName
     *
     * @return void
     */
    public function resetIndex($indexName);
}
