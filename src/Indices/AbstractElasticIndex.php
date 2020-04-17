<?php

namespace Lelastico\Indices;

use Elasticsearch\Client;

abstract class AbstractElasticIndex
{
    use WorksWithDocuments;
    use WorksWithIndex;

    /**
     * Elastic index name with prefix.
     *
     * @var string
     */
    public $name;

    /**
     * Elastic index name without prefix.
     *
     * @var string
     */
    public $cleanName;

    /**
     * @var Client
     */
    public $client;

    /**
     * AbstractIndex constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = null === $client ? resolve(Client::class) : $client;

        // Try to build the index prefix and if not empty, then use _ as separator to
        // match ELK convention.
        $indexPrefix = config('lelastico.prefix');

        $this->cleanName = $this->createIndexName();
        if ('' !== $indexPrefix) {
            $this->name = $indexPrefix.'_'.$this->cleanName;
        } else {
            $this->name = $this->cleanName;
        }
    }

    /**
     * Builds an index name (called on construct).
     *
     * @return string
     */
    abstract protected function createIndexName(): string;
}
