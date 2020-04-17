<?php

namespace Lelastico\Indices;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Support\Str;
use Lelastico\Write\BulkWrite;

/**
 * @property Client client
 */
trait WorksWithDocuments
{
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
     * @param int   $id
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
     * Updates given document.
     *
     * @param int   $id
     * @param array $document
     *
     * @return array|callable
     */
    public function updateDocument(int $id, array $document)
    {
        return $this->client->update([
            'body' => ['doc' => $document],
            'index' => $this->name,
            'id' => $id,
        ]);
    }

    /**
     * Creates or updates given document.
     *
     * @param int   $id
     * @param array $document
     *
     * @return array|callable
     */
    public function createOrUpdateDocument(int $id, array $document)
    {
        if ($this->client->exists([
            'index' => $this->name,
            'id' => $id,
        ])) {
            return $this->updateDocument($id, $document);
        }

        return $this->createDocument($id, $document);
    }

    /**
     * Deletes documents by given query.
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
