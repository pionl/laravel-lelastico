<?php

declare(strict_types=1);

namespace Lelastico\Indices;

use Elasticsearch\Client;

abstract class AbstractElasticIndex
{
    use WorksWithDocuments;
    use WorksWithIndex;

    /**
     * Elastic index name with prefix.
     */
    public string $name;

    /**
     * Elastic index name without prefix.
     */
    public string $cleanName;

    public function __construct(public Client $client)
    {
        // Try to build the index prefix and if not empty, then use _ as separator to
        // match ELK convention.
        $indexPrefix = config('lelastico.prefix');

        $this->cleanName = $this->createIndexName();
        $this->name = $indexPrefix !== '' ? $indexPrefix . '_' . $this->cleanName : $this->cleanName;
    }

    /**
     * Builds an index name (called on construct).
     */
    abstract protected function createIndexName(): string;
}
