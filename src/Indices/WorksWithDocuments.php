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
     * $options['type']                   = DEPRECATED (list) A comma-separated list of document types to search; leave empty to perform the operation on all types
     * $options['analyzer']               = (string) The analyzer to use for the query string
     * $options['analyze_wildcard']       = (boolean) Specify whether wildcard and prefix queries should be analyzed (default: false)
     * $options['default_operator']       = (enum) The default operator for query string query (AND or OR) (Options = AND,OR) (Default = OR)
     * $options['df']                     = (string) The field to use as default where no field prefix is given in the query string
     * $options['from']                   = (number) Starting offset (default: 0)
     * $options['ignore_unavailable']     = (boolean) Whether specified concrete indices should be ignored when unavailable (missing or closed)
     * $options['allow_no_indices']       = (boolean) Whether to ignore if a wildcard indices expression resolves into no concrete indices. (This includes `_all` string or when no indices have been specified)
     * $options['conflicts']              = (enum) What to do when the delete by query hits version conflicts? (Options = abort,proceed) (Default = abort)
     * $options['expand_wildcards']       = (enum) Whether to expand wildcard expression to concrete indices that are open, closed or both. (Options = open,closed,hidden,none,all) (Default = open)
     * $options['lenient']                = (boolean) Specify whether format-based query failures (such as providing text to a numeric field) should be ignored
     * $options['preference']             = (string) Specify the node or shard the operation should be performed on (default: random)
     * $options['q']                      = (string) Query in the Lucene query string syntax
     * $options['routing']                = (list) A comma-separated list of specific routing values
     * $options['scroll']                 = (time) Specify how long a consistent view of the index should be maintained for scrolled search
     * $options['search_type']            = (enum) Search operation type (Options = query_then_fetch,dfs_query_then_fetch)
     * $options['search_timeout']         = (time) Explicit timeout for each search request. Defaults to no timeout.
     * $options['size']                   = (number) Deprecated, please use `max_docs` instead
     * $options['max_docs']               = (number) Maximum number of documents to process (default: all documents)
     * $options['sort']                   = (list) A comma-separated list of <field>:<direction> pairs
     * $options['_source']                = (list) True or false to return the _source field or not, or a list of fields to return
     * $options['_source_excludes']       = (list) A list of fields to exclude from the returned _source field
     * $options['_source_includes']       = (list) A list of fields to extract and return from the _source field
     * $options['terminate_after']        = (number) The maximum number of documents to collect for each shard, upon reaching which the query execution will terminate early.
     * $options['stats']                  = (list) Specific 'tag' of the request for logging and statistical purposes
     * $options['version']                = (boolean) Specify whether to return document version as part of a hit
     * $options['request_cache']          = (boolean) Specify if request cache should be used for this request or not, defaults to index level setting
     * $options['refresh']                = (boolean) Should the effected indexes be refreshed?
     * $options['timeout']                = (time) Time each individual bulk request should wait for shards that are unavailable. (Default = 1m)
     * $options['wait_for_active_shards'] = (string) Sets the number of shard copies that must be active before proceeding with the delete by query operation. Defaults to 1, meaning the primary shard only. Set to `all` for all shard copies, otherwise set to any non-negative value less than or equal to the total number of copies for the shard (number of replicas + 1)
     * $options['scroll_size']            = (number) Size on the scroll request powering the delete by query (Default = 100)
     * $options['wait_for_completion']    = (boolean) Should the request should block until the delete by query is complete. (Default = true)
     * $options['requests_per_second']    = (number) The throttle for this request in sub-requests per second. -1 means no throttle. (Default = 0)
     * $options['slices']                 = (number|string) The number of slices this task should be divided into. Defaults to 1, meaning the task isn't sliced into subtasks. Can be set to `auto`. (Default = 1)
     *
     * @param array $query
     * @param array $options Additional params for ES client
     *
     * @return array Result from Client::deleteByQuery()
     *
     * @throws ElasticsearchException
     *
     * @example $this->index->deleteByQuery([
     *              'match' => [
     *                  'object_id' => $this->object->getKey(),
     *              ],
     *          ]);
     */
    public function deleteByQuery(array $query, array $options = []): array
    {
        $params = $options + [
            'index' => $this->name,
            'body' => [
                'query' => $query,
            ],
        ];

        try {
            return $this->client->deleteByQuery($params);
        } catch (Missing404Exception $exception) {
            $indexNotFound = Str::contains($exception->getMessage(), 'index_not_found_exception');

            // Ignore index not found
            if ($indexNotFound) {
                return [];
            }

            throw $exception;
        }
    }
}
