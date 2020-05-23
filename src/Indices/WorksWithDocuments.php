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
     * $options['wait_for_active_shards'] = (string) Sets the number of shard copies that must be active before proceeding with the index operation. Defaults to 1, meaning the primary shard only. Set to `all` for all shard copies, otherwise set to any non-negative value less than or equal to the total number of copies for the shard (number of replicas + 1)
     * $options['refresh']                = (enum) If `true` then refresh the affected shards to make this operation visible to search, if `wait_for` then wait for a refresh to make this operation visible to search, if `false` (the default) then do nothing with refreshes. (Options = true,false,wait_for)
     * $options['routing']                = (string) Specific routing value
     * $options['timeout']                = (time) Explicit operation timeout
     * $options['version']                = (number) Explicit version number for concurrency control
     * $options['version_type']           = (enum) Specific version type (Options = internal,external,external_gte)
     * $options['pipeline']               = (string) The pipeline id to preprocess incoming documents with
     *
     * @param int   $id
     * @param array $document
     * @param array $options
     *
     * @return array|callable
     */
    public function createDocument(int $id, array $document, array $options = [])
    {
        return $this->client->create($options + [
            'body' => $document,
            'index' => $this->name,
            'id' => $id,
        ]);
    }

    /**
     * Updates given document.
     *
     * $options['wait_for_active_shards'] = (string) Sets the number of shard copies that must be active before proceeding with the index operation. Defaults to 1, meaning the primary shard only. Set to `all` for all shard copies, otherwise set to any non-negative value less than or equal to the total number of copies for the shard (number of replicas + 1)
     * $options['refresh']                = (enum) If `true` then refresh the affected shards to make this operation visible to search, if `wait_for` then wait for a refresh to make this operation visible to search, if `false` (the default) then do nothing with refreshes. (Options = true,false,wait_for)
     * $options['routing']                = (string) Specific routing value
     * $options['timeout']                = (time) Explicit operation timeout
     * $options['version']                = (number) Explicit version number for concurrency control
     * $options['version_type']           = (enum) Specific version type (Options = internal,external,external_gte)
     * $options['pipeline']               = (string) The pipeline id to preprocess incoming documents with
     *
     * @param int   $id
     * @param array $document
     * @param array $options
     *
     * @return array|callable
     */
    public function updateDocument(int $id, array $document, array $options = [])
    {
        return $this->client->update($options + [
            'body' => ['doc' => $document],
            'index' => $this->name,
            'id' => $id,
        ]);
    }

    /**
     * Creates or updates given document.
     *
     * $options['wait_for_active_shards'] = (string) Sets the number of shard copies that must be active before proceeding with the index operation. Defaults to 1, meaning the primary shard only. Set to `all` for all shard copies, otherwise set to any non-negative value less than or equal to the total number of copies for the shard (number of replicas + 1)
     * $options['refresh']                = (enum) If `true` then refresh the affected shards to make this operation visible to search, if `wait_for` then wait for a refresh to make this operation visible to search, if `false` (the default) then do nothing with refreshes. (Options = true,false,wait_for)
     * $options['routing']                = (string) Specific routing value
     * $options['timeout']                = (time) Explicit operation timeout
     * $options['version']                = (number) Explicit version number for concurrency control
     * $options['version_type']           = (enum) Specific version type (Options = internal,external,external_gte)
     * $options['pipeline']               = (string) The pipeline id to preprocess incoming documents with
     *
     * @param int   $id
     * @param array $document
     * @param array $options
     *
     * @return array|callable
     */
    public function createOrUpdateDocument(int $id, array $document, array $options = [])
    {
        if ($this->client->exists([
            'index' => $this->name,
            'id' => $id,
        ])) {
            return $this->updateDocument($id, $document, $options);
        }

        return $this->createDocument($id, $document, $options);
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
