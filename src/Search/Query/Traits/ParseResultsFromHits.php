<?php

declare(strict_types=1);

namespace Lelastico\Search\Query\Traits;

trait ParseResultsFromHits
{
    /**
     * Returns an array of _source items from $hits.
     */
    public function getResultsFromHits(array $hits): array
    {
        return array_map(fn ($result) => $this->getSourceItem($result), $hits);
    }

    /**
     * Returns _source value from $result.
     *
     * @return array|mixed
     */
    protected function getSourceItem(array $result)
    {
        return $result['_source'];
    }
}
