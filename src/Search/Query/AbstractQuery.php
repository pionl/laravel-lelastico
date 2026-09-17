<?php

declare(strict_types=1);

namespace Lelastico\Search\Query;

use Erichard\ElasticQueryBuilder\Contracts\QueryInterface;

abstract class AbstractQuery
{
    /**
     * Adds the query filter to must not filter in elastic search.
     */
    public bool $scoring = false;

    abstract public function createQuery(): ?QueryInterface;

    /**
     * Does filter counts to scoring?
     */
    public function setScoring(bool $scoring): self
    {
        $this->scoring = $scoring;

        return $this;
    }
}
