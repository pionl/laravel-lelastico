<?php

namespace Lelastico\Search\Query\Traits;

use Lelastico\Search\Query\AbstractBuilder;

trait HasPaginationSettings
{
    /**
     * @var int
     */
    protected $currentPage = 1;

    /**
     * @var int
     */
    protected $perPage = 10;

    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @param int $page
     *
     * @return AbstractBuilder
     */
    public function setCurrentPage(int $page): self
    {
        $this->currentPage = $page;

        return $this;
    }
}
