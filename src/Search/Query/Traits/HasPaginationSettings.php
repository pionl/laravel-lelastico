<?php

declare(strict_types=1);

namespace Lelastico\Search\Query\Traits;

trait HasPaginationSettings
{
    protected int $currentPage = 1;

    protected int $perPage = 10;

    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function setCurrentPage(int $page): self
    {
        $this->currentPage = $page;

        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
