<?php

declare(strict_types=1);

namespace Lelastico\Write;

use Closure;
use Elasticsearch\Client;
use Lelastico\Indices\AbstractElasticIndex;

/**
 * Adds ability to add documents and write them in a batch request.
 */
class BulkWrite
{
    public int $documentsCount = 0;

    protected float $startTime;

    private array $documents = [];

    public function __construct(
        private AbstractElasticIndex $index,
        private Client $client,
        /**
         * Receives response and time
         *
         * @var Closure<array, float)|null
         */
        private Closure|null $onSent = null,
        /**
         * Called when documents are sent. Receives response and start time for measuring how long it took to build 1
         * chunk.
         */
        public bool $refresh = true,
        /**
         * Sends a chunk of documents when document is added.
         */
        public int $chunkSize = 500,
    ) {
        $this->startTime = microtime(true);
    }

    /**
     * Adds new document and sends a chunk of documents if needed.
     *
     * @return $this
     */
    public function addDocument(array $document, string $id): self
    {
        ++$this->documentsCount;
        // Add the document data with index / id mapping.
        $this->documents[] = [
            'index' => [
                '_index' => $this->index->name,
                '_id' => $id,

            ],
        ];
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
    public function finish(): void
    {
        if ($this->documents !== []) {
            $this->sendDocuments();
        }
    }

    /**
     * Sends the current documents and resets them.
     */
    protected function sendDocuments(): array
    {
        $response = $this->client->bulk([
            'body' => $this->documents,
            'index' => $this->index->name,
            'refresh' => $this->refresh,
        ]);

        if ($this->onSent !== null) {
            call_user_func($this->onSent, $response, $this->startTime);
        }

        $this->startTime = microtime(true);

        $this->documents = [];

        return $response;
    }
}
