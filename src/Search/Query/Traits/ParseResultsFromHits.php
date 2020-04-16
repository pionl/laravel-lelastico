<?php

namespace Lelastico\Search\Query\Traits;

trait ParseResultsFromHits
{
    /**
     * Returns an array of _source items from $hits.
     *
     * @param array $hits
     *
     * @return array
     */
    protected function getResultsFromHits(array $hits): array
    {
        return array_map(function ($result) {
            return $this->getSourceItem($result);
        }, $hits);
    }

    /**
     * Returns _source value from $result.
     *
     * @param array $result
     *
     * @return array
     */
    protected function getSourceItem(array $result): array
    {
        return $result['_source'];
    }
}
