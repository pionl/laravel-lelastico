<?php

namespace Lelastico\Indices;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Support\Str;
use Lelastico\Write\BulkWrite;

abstract class AbstractElasticIndex
{
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

    /**
     * Returns the properties mapping.
     *
     * @return array
     */
    protected function propertyMappings(): array
    {
        return [];
    }

    /**
     * Returns settings for the index.
     *
     * @return array
     */
    protected function settings(): array
    {
        return [];
    }

    /**
     * Creates an index.
     */
    public function create()
    {
        $body = [];

        // Add mappings
        $properties = $this->propertyMappings();
        if (!empty($properties)) {
            $body['mappings'] = [
                'properties' => $properties,
            ];
        }

        // Add settings
        $settings = $this->settings();
        if (!empty($settings)) {
            $body['settings'] = $settings;
        }

        // Create index
        $this->client->indices()->create([
            'index' => $this->name,
            'body' => $body,
        ]);
    }

    /**
     * Update mappings (if contains) and settings.
     */
    public function update()
    {
        // Update mapping
        $properties = $this->propertyMappings();
        if (!empty($properties)) {
            $this->client->indices()->putMapping([
                'index' => $this->name,
                'body' => [
                    'properties' => $properties,
                ],
            ]);
        }

        // Update settings
        $settings = $this->settings();
        if (!empty($settings)) {
            $this->client->indices()->putSettings([
                'index' => $this->name,
                'body' => $settings,
            ]);
        }
    }

    /**
     * Deletes the index.
     *
     * @return array
     */
    public function delete(): array
    {
        return $this->client->indices()->delete([
            'index' => $this->name,
        ]);
    }

    /**
     * Checks if the index exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->client->indices()->exists([
            'index' => $this->name,
        ]);
    }

    /**
     * Creates a bulk write instance for writing documents in bulk.
     *
     * @param callable|null $onSent
     *
     * @return BulkWrite
     */
    public function bulkWrite(callable $onSent = null): BulkWrite
    {
        return new BulkWrite($this, $this->client, $onSent);
    }

    /**
     * Creates a document.
     *
     * @param array $document
     *
     * @return array|callable
     */
    public function createDocument(int $id, array $document)
    {
        return $this->client->create([
            'body' => $document,
            'index' => $this->name,
            'id' => $id,
        ]);
    }

    /**
     * Deletes entries by given query.
     *
     * @param array $query
     *
     * @throws ElasticsearchException
     *
     * @example $this->index->deleteByQuery([
     *              'match' => [
     *                  'object_id' => $this->object->getKey(),
     *              ],
     *          ]);
     */
    public function deleteByQuery(array $query)
    {
        try {
            $this->client->deleteByQuery([
                'index' => $this->name,
                'body' => [
                    'query' => $query,
                ],
            ]);
        } catch (Missing404Exception $exception) {
            // If the index was yet created - ignore
            if (false === Str::contains($exception->getMessage(), 'index_not_found_exception')) {
                throw $exception;
            }
        }
    }
}
