<?php

namespace Lelastico\Write;

use Elasticsearch\Client;
use Lelastico\Indices\AbstractElasticIndex;

/**
 * Adds ability to add documents and write them in a batch request.
 */
class BulkWrite
{
    /**
     * @var float|string
     */
    protected $startTime;

    /**
     * @var AbstractElasticIndex
     */
    private $index;

    private $documents = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    public $documentsCount = 0;

    /**
     * Sends a chunk of documents when document is added.
     *
     * @var int
     */
    public $chunkSize = 500;
    /**
     * @var callable|null
     */
    private $onSent;

    /**
     * BulkWrite constructor.
     *
     * @param AbstractElasticIndex $index
     * @param Client               $client
     * @param callable|null        $onSent Called when documents are sent. Receives response and start time for measuring
     *                                     how long it took to build 1 chunk.
     */
    public function __construct(AbstractElasticIndex $index, Client $client, callable $onSent = null)
    {
        $this->index = $index;
        $this->client = $client;
        $this->onSent = $onSent;
        $this->startTime = microtime(true);
    }

    /**
     * Adds new document and sends a chunk of documents if needed.
     *
     * @param array  $document
     * @param string $id
     *
     * @return $this
     */
    public function addDocument(array $document, string $id)
    {
        ++$this->documentsCount;
        // Add the document data with index / id mapping.
        $this->documents[] = ['index' => ['_index' => $this->index->name, '_id' => $id]];
        $this->documents[] = $document;

        // Sent a chunk of the documents (1 document === 2 entries in array)
        if (($this->chunkSize * 2) === count($this->documents)) {
            $this->sendDocuments();
        }

        return $this;
    }

    /**
     * Finishes the bulk write (sends the reset of the documents).
     */
    public function finish()
    {
        if (count($this->documents) > 0) {
            $this->sendDocuments();
        }
    }

    /**
     * Sends the current documents and resets them.
     *
     * @return array
     */
    protected function sendDocuments(): array
    {
        $response = $this->client->bulk([
            'body' => $this->documents,
            'index' => $this->index->name,
        ]);

        if (null !== $this->onSent) {
            call_user_func($this->onSent, $response, $this->startTime);
        }

        $this->startTime = microtime(true);

        $this->documents = [];

        return $response;
    }
}
